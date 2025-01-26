<h1>New Booking Notification</h1>
<p>A new booking has been created with the following details:</p>
<ul>
    <li><strong>Name:</strong> {{ $booking->name }}</li>
    <li><strong>Email:</strong> {{ $booking->email }}</li>
    <li><strong>Phone:</strong> {{ $booking->phone }}</li>
    <li><strong>Members:</strong> {{ $booking->members }}</li>
    <li><strong>Place:</strong> {{ $booking->place }}</li>
    <li><strong>Days:</strong> {{ $booking->days }}</li>
    <li><strong>Date:</strong> {{ $booking->date }}</li>
    <li><strong>Time:</strong> {{ $booking->time }}</li>
    <li><strong>Tour Code:</strong> {{ $booking->TourCode }}</li>
</ul>
