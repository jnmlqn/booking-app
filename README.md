<h2>HOW TO RUN THE APPLICATION</h2>
1. Create a .env file by copying the contents of .env.example
2. Create a new database named 'booking'
3. At the root of this folder, open a new terminal
4. Run `composer install`
5. Run `npm install`
6. Run `php artisan migrate` to create the tables
7. Run `php artisan db:seed` to create a dummy data
8. Run `npm run dev` to generate the frontend resources
9. Open http://localhost:8000` in your favorite browser