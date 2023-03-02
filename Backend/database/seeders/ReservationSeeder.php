<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Container\Container;

class ReservationSeeder extends Seeder
{
    /**
     * @var Faker
     */
    private Faker $faker;

    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::limit(100)->get();
        $rooms = Room::get();
        $roomIds = $rooms->pluck('id');

        foreach ($users as $user) {
            foreach ($roomIds as $roomId) {
                $startTime = $this->faker->time();
                Reservation::create([
                    'room_id' => $roomId,
                    'user_id' => $user->id,
                    'reservation_date' => $this->faker->date(),
                    'start_time' => $startTime,
                    'end_time' => date('H:i:s', strtotime($startTime . ' + 15 minute'))
                ]);
            }
        }
    }

    /**
     * @return Faker
     */
    protected function withFaker(): Faker
    {
        return Container::getInstance()->make(Faker::class);
    }
}
