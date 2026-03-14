<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function facilityManagement()
    {
        return view('dashboards.admin.settings.facilities');
    }

    public function show(Facility $facility)
    {
        $facility->load(['primaryImage', 'images']);
        return view('dashboards.admin.settings.facilityShow', compact('facility'));
    }

    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $query = Facility::with('primaryImage');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        return response()->json([
            'facilities' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => 'required|string|max:50',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'primary_index' => 'nullable|integer|min:0',
        ]);

        $facility = Facility::create($validated);

        if ($request->hasFile('images')) {
            $created = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('facilities/gallery', 'public');
                $created[] = $facility->images()->create([
                    'image_path' => $path,
                    'alt_text' => $facility->title,
                    'sort_order' => 0,
                ]);
            }

            $primaryIndex = $request->integer('primary_index', 0);
            if (isset($created[$primaryIndex])) {
                $facility->update(['primary_image_id' => $created[$primaryIndex]->id]);
            } elseif (!empty($created)) {
                $facility->update(['primary_image_id' => $created[0]->id]);
            }
        }

        return response()->json(['message' => 'Facility created successfully']);
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => 'required|string|max:50',
        ]);

        $facility->update($validated);

        return response()->json(['message' => 'Facility updated successfully']);
    }

    public function destroy(Facility $facility)
    {
        $facility->images()->each(function ($image) {
            Storage::disk('public')->delete($image->image_path);
        });
        $facility->delete();

        return response()->json(['message' => 'Facility deleted successfully']);
    }

    public function images(Facility $facility)
    {
        return response()->json([
            'images' => $facility->images()->orderBy('sort_order')->get(),
            'primary_image_id' => $facility->primary_image_id,
        ]);
    }

    public function publicIndex()
    {
        $facilities = Facility::with('primaryImage')
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'facilities' => $facilities,
        ]);
    }

    public function publicShow(Facility $facility)
    {
        $facility->load(['primaryImage', 'images']);

        return response()->json([
            'facility' => $facility,
        ]);
    }

    public function uploadImages(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120',
        ]);

        $created = [];
        foreach ($validated['images'] as $file) {
            $path = $file->store('facilities/gallery', 'public');
            $created[] = $facility->images()->create([
                'image_path' => $path,
                'alt_text' => $facility->title,
                'sort_order' => 0,
            ]);
        }

        if (!$facility->primary_image_id && count($created)) {
            $facility->update(['primary_image_id' => $created[0]->id]);
        }

        return response()->json([
            'message' => 'Images uploaded',
            'images' => $facility->images()->orderBy('sort_order')->get(),
            'primary_image_id' => $facility->primary_image_id,
        ]);
    }

    public function deleteImage(Facility $facility, FacilityImage $image)
    {
        if ($image->facility_id !== $facility->id) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        Storage::disk('public')->delete($image->image_path);
        $wasPrimary = $facility->primary_image_id === $image->id;
        $image->delete();

        if ($wasPrimary) {
            $next = $facility->images()->orderBy('sort_order')->first();
            $facility->update(['primary_image_id' => $next?->id]);
        }

        return response()->json([
            'message' => 'Image deleted',
            'images' => $facility->images()->orderBy('sort_order')->get(),
            'primary_image_id' => $facility->primary_image_id,
        ]);
    }

    public function setPrimaryImage(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'image_id' => 'required|integer|exists:facility_images,id',
        ]);

        $image = FacilityImage::where('id', $validated['image_id'])
            ->where('facility_id', $facility->id)
            ->firstOrFail();

        $facility->update(['primary_image_id' => $image->id]);

        return response()->json([
            'message' => 'Primary image updated',
            'primary_image_id' => $facility->primary_image_id,
        ]);
    }
}
