<h2>HOW TO RUN THE APPLICATION</h2>
<ul>
	<li>Create a .env file by copying the contents of .env.example</li>
	<li>Create a new database named 'booking'</li>
	<li>At the root of this folder, open a new terminal</li>
	<li>Run `composer install`</li>
	<li>Run `npm install`</li>
	<li>Run `php artisan migrate` to create the tables</li>
	<li>Run `php artisan db:seed` to create a dummy data</li>
	<li>Run `npm run dev` to generate the frontend resources</li>
	<li>Open http://localhost:8000` in your favorite browser</li>
</ul>

<h2>Additional Question</h2>
<p>If given more time, what other features would you like to incorporate?</p>
<ul>
	<li>Improve the user interface and user experience</li>
	<li>Will not use the SPA approach for a better security</li>
	<li>Will add the actual reservations per date, instead of showing a generic error message if the room and date is not available</li>
	<li>Will add email confirmation before allowing the user to login</li>
	<li>Will add a change password option</li>
	<li>Will add more user details</li>
	<li>Will add an admin interface wherein a different user can add more rooms and adjust the availability of the rooms</li>
</ul>