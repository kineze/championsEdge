<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
    <div style="padding: 30px;">
      <h2 style="margin-top: 0;">Your Password Was Reset</h2>
      <p>Hi <strong>{{ $name }}</strong>,</p>
      <p>Your new password is: <strong>{{ $password }}</strong></p>
      <p>If this wasn’t you, please contact our support team immediately.</p>
      <div style="margin: 30px 0; text-align: center;">
        <a href="{{ url('/login') }}" style="background: #000; color: #fff; padding: 12px 25px; border-radius: 5px; text-decoration: none;">Login Now</a>
      </div>
    </div>
  </div>
</div>
