<?php

namespace App\Services;

use App\Repositories\UsersRepository;

class UsersService
{
    /**
    * @var UsersRepository
    */
    private UsersRepository $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    /**
     * @param  string  $name
     * @param  string  $email
     * @param  string  $password
     * 
     * @return void
     * @throws \Exception
     */
    public function register(
        string $name,
        string $email,
        string $password
    ): void {
        $this->usersRepository->register(
            $name,
            $email,
            $password
        );     
    }
}
