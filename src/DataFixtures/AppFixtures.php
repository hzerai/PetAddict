<?php

namespace App\DataFixtures;

use App\Enums\Animals;
use App\Entity\Adoption;
use App\Entity\Animal;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {

        $generator = Factory::create("fr_FR");
        for ($i = 0; $i <= 100; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@gmail.com');
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    'password'
                )
            );
            $user->setPhoneNumber($generator->phoneNumber);
            $user->setAbout($generator->text);
            $user->setFirstName($generator->firstName);
            $user->setLastName($generator->lastName);
            $user->setBirthDate(
                $generator->dateTimeBetween('1950-01-01', '2012-12-31')
            );
            $user->setEmailVerified(true);
            $adoption = new Adoption();
            $adoption->setTitle($generator->sentence($nbWords = 6, $variableNbWords = true));
            $adoption->setDescription($generator->text);
            $animal = new Animal();
            $animal->setSexe($i % 2 == 0 ? 'femenin' : 'masculin');
            $animal->setType($generator->word);
            $animal->setNom($generator->firstName);
            $animal->setAge($generator->randomNumber(1));
            $animal->setCouleur($generator->colorName);
            if ($i % 5 == 0) {
                $animal->setEspece(Animals::FISH);
                $animal->setTaille('Petite');
                $user->setFavoriteAnimal(Animals::FISH);
            } else if ($i % 4 == 0) {
                $animal->setEspece(Animals::BIRD);
                $animal->setTaille('Petite');

                $user->setFavoriteAnimal(Animals::BIRD);
            } else if ($i % 3 == 0) {
                $animal->setEspece(Animals::CAT);
                $animal->setTaille('Moyen');

                $user->setFavoriteAnimal(Animals::CAT);
            } else if ($i % 2 == 0) {
                $animal->setEspece(Animals::DOG);
                $animal->setTaille('Grande');

                $user->setFavoriteAnimal(Animals::DOG);
            } else {
                $animal->setEspece(Animals::TURTLE);
                $animal->setTaille('Moyen');

                $user->setFavoriteAnimal(Animals::TURTLE);
            }
            $manager->persist($animal);

            $adoption->setAnimal($animal);
            $manager->persist($user);
            $manager->flush();
            $adoption->setUserId($user->getId());
            $manager->persist($adoption);
        }

        $manager->flush();
    }
}
