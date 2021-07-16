<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\AddressDetails\Villes;
use App\Enums\Animals;
use App\Entity\Adoption;
use App\Entity\Animal;
use App\Entity\User;
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
        $villes = Villes::GET();

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
            $address = new Address();
            $address->setVille($villes[$i % 22]->name);
            $address->setMunicipality($villes[$i % 23]->municipalities[$i % 4]->name);
            $address->setCreatedBy($user->getEmail());

            $adoption = new Adoption();
            $adoption->setCreatedBy($user->getEmail());
            $adoption->setTitle($generator->sentence($nbWords = 6, $variableNbWords = true));
            $adoption->setDescription($generator->text);
            $animal = new Animal();
            $animal->setSexe($i % 2 == 0 ? 'femenin' : 'masculin');
            $animal->setType($generator->word);
            $animal->setNom($generator->firstName);
            $animal->setCreatedBy($user->getEmail());

            if ($i % 10 < 2) {
                $animal->setAge('Bébé');
            } else if ($i % 10 < 4) {
                $animal->setAge('Junior');
            } else if ($i % 10 < 6) {
                $animal->setAge('Adulte');
            } else {
                $animal->setAge('Senior');
            }
            if ($i % 10 == 0) {
                $animal->setCouleur('Beige');
            } else if ($i % 10 == 1) {
                $animal->setCouleur('Blanc');
            } else if ($i % 10 == 2) {
                $animal->setCouleur('Noir');
            } else if ($i % 10 == 3) {
                $animal->setCouleur('Gris');
            } else  if ($i % 10 == 4) {
                $animal->setCouleur('Roux');
            } else if ($i % 10 == 5) {
                $animal->setCouleur('Tigréroux');
            } else if ($i % 10 == 6) {
                $animal->setCouleur('Roux et blanc');
            } else if ($i % 10 == 7) {
                $animal->setCouleur('Pluri coloré');
            } else if ($i % 10 == 8) {
                $animal->setCouleur('Noir avec marque blanche');
            } else if ($i % 10 == 9) {
                $animal->setCouleur('Couleur indéfinie');
            }

            if ($i % 5 == 0) {
                $adoption->setUrgent(true);
                $animal->setEspece(Animals::FISH);
                $animal->setTaille('Très petite');
                $user->setFavoriteAnimal(Animals::FISH);
            } else if ($i % 4 == 0) {
                $adoption->setStatus('ADOPTED');
                $animal->setEspece(Animals::BIRD);
                $animal->setTaille('Très Grande');
                $user->setFavoriteAnimal(Animals::BIRD);
            } else if ($i % 3 == 0) {
                $animal->setEspece(Animals::CAT);
                $animal->setTaille('Grande');
                $user->setFavoriteAnimal(Animals::CAT);
                if ($i % 10 < 2) {
                    $animal->settype('Abyssin');
                } else if ($i % 10 < 4) {
                    $animal->settype('American Bobtail');
                } else if ($i % 10 < 6) {
                    $animal->settype('American Curl');
                } else if ($i % 10 < 8) {
                    $animal->settype('American Shorthair');
                } else {
                    $animal->settype('Angora');
                }
            } else if ($i % 2 == 0) {
                $animal->setEspece(Animals::DOG);
                $animal->setTaille('Moyenne');
                $user->setFavoriteAnimal(Animals::DOG);

                if ($i % 10 < 2) {
                    $animal->settype('Berger');
                } else if ($i % 10 < 4) {
                    $animal->settype('Labrador');
                } else if ($i % 10 < 6) {
                    $animal->settype('Husky');
                } else if ($i % 10 < 8) {
                    $animal->settype('Caniche(Moyen)');
                } else {
                    $animal->settype('Bulldog');
                }
            } else {
                $animal->setEspece('Cheval');
                $animal->setTaille('Petite');
                $user->setFavoriteAnimal(Animals::TURTLE);
                if ($i % 10 < 2) {
                    $animal->settype('Andalou');
                } else if ($i % 10 < 4) {
                    $animal->settype('Barbe');
                } else if ($i % 10 < 6) {
                    $animal->settype('Anglo arabe');
                } else if ($i % 10 < 8) {
                    $animal->settype('Selle Français');
                } else {
                    $animal->settype('Race indéfinie');
                }
            }
            $manager->persist($animal);
            $adoption->setAnimal($animal);
            $manager->persist($address);
            $manager->flush();
            $user->setAddressId($address->getId());
            $manager->persist($user);
            $manager->flush();
            $adoption->setUserId($user->getId());
            $manager->persist($adoption);
        }
        $manager->flush();
    }
}
