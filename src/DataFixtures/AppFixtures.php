<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;
use App\Entity\Subject;
use App\Entity\Answer;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Boucle qui crée mes utilisateurs
        for ($i=1; $i < 5; $i++) { 
            $user = new User();
            $user->setEmail("useremail" . $i . "@exemple.com");
            $password = $this->encoder->encodePassword($user, "password" . $i);
            $user->setPassword($password);
            $user->setFirstname("Firstname" . $i);
            $user->setLastname("Lastname" . $i);
            // Génère un nombre aléatoire de sujet pour l'utilisateur
            for ($j=1; $j < mt_rand(1, 6); $j++) { 
                $subject = new Subject();
                $subject->setTitle("Title" . $j . " User" . $i);
                $subject->setContent("Some written long content" . $j . " User" . $i);
                $subject->setPublished(new \DateTime());
                $subject->setUser($user);
                $manager->persist($subject);
            }
            $manager->persist($user);
        }
        $manager->flush();

        $userRepository = $manager->getRepository(User::class);
        $subjectRepository = $manager->getRepository(Subject::class);

        $users = $userRepository->findAll();
        $subjects = $subjectRepository->findAll();
        // Cette boucle aurait pu être imbriquée dans les deux premières
        // Mais cela permet d'associer les réponses à des utilisateurs au harsard
        // Une autre solution aurait été de faire plusieurs fichiers de fixtures
        foreach ($subjects as $key => $subject) {
            for ($i=1; $i < 10; $i++) { 
                $answer = new Answer();
                $answer->setContent("Some nice answer" . $i);
                $answer->setPublished(new \DateTime());
                $answer->setUser($user);
                $answer->setSubject($subject);
                $answer->setUser($users[mt_rand(0, count($users)-1)]);
                $manager->persist($answer);
            }
        }
        $manager->flush();
    }
}
