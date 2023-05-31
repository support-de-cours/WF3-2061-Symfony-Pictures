<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    const DATA = [
        [
            'firstname' => "John",
            'lastname' => "DOE",
            'email' => "john@doe.com",
            'password' => "123456",
        ],
        [
            'firstname' => "Jane",
            'lastname' => "DOE",
            'email' => "jane@doe.com",
            'password' => "123456",
        ],
    ];

    /**
     * Password Encoder
     *
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $encoder;
    
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::DATA as $item)
        {
            $user = new User;

            // Generate password Hash
            $password = $this->encoder->hashPassword($user, $item['password']);

            $user->setFirstname($item['firstname']);
            $user->setLastname($item['lastname']);
            $user->setEmail($item['email']);

            $user->setPassword($password);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
