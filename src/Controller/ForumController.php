<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subject;
use App\Entity\Answer;
use App\Entity\Comment;
use App\Form\SubjectType;
use App\Form\AnswerType;
use App\Form\CommentType;
use App\Form\RegistrationFormType;
use App\Repository\SubjectRepository;
use App\Repository\AnswerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class ForumController extends AbstractController
{
    /**
     * On restreint l'accès à la seule méthode get (url), pas de formulaire possible (post)
     * @Route("/forum", methods={"GET"}, name="forum")
     * @Route("/", methods={"GET"}, name="index")
     */
    public function index(): Response
    {
        // On récupère le repo (le manger/model) de l'entité Subject, ce repo contient déjà des requêtes simples en BDD
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        // Sur le repo on appelle la méthode findBy qui renvoie toutes les entités (ici Subject) selon certains critères
        // Ici on prend les 5 derniers id enregistrés
        $subjects = $subjectRepository->findBy(
            [],
            ["id" => "DESC"],
            10
        );
        // On retourne une vue sous forme de réponse et on lui passe une variables subjects à laquelle on associe $subjects
        return $this->render('forum/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/forum/subject/{id}", methods={"GET", "POST"}, name="single", requirements={"id"="\d+"})
     */
    // Méthode pour afficher un sujet. Elle attend un paramètre id car la route attend un paramètre
    // On précise dans la route et la méthode que ce paramètre est un integer
    public function single(int $id=1, SubjectRepository $subjectRepository, Request $request): Response
    {
        // Contrairement à l'index ici le repo de l'entité à été passé directement en paramètre
        // On fait appelle à la méthode find du repo qui recherche une entité par sa clef primaire
        $subject = $subjectRepository->find($id);

        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $answer->setPublished(new \DateTime());
            $answer->setUser($this->getUser());
            $answer->setSubject($subject);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($answer);
            $entityManager->flush();
        }

        return $this->render('forum/single.html.twig', [
            "subject" => $subject,
            "form" => $form->createView()
        ]);
    }
    
    /**
     * @Route("/forum/rules", methods={"GET"}, name="rules")
     */
    public function rules(): Response
    {
        return $this->render('forum/rules.html.twig', [
        ]);
    }

    /**
     * @Route("/forum/subject/new", methods={"GET", "POST"}, name="newSubject")
     */
    public function newSubject(Request $request): Response
    {
        // On crée un nouveau Subject vide et un formulaire sur la base de cette entité
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        // On traite les données soumises lors de la requêtes dans l'objet form
        $form->handleRequest($request);
        // Si on a soumis un formulaire et que tout est OK
        if($form->isSubmitted() && $form->isValid()) {
            $subject->setPublished(new \DateTime());
            // On associe au sujet, l'utilisateur connecté qu'on récupère via le controller
            $subject->setUser($this->getUser());
            // On enregistre le nouveau sujet
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subject);
            // Attention les requêtes ne sont exécutées que lors du flush donc à ne pas oublier
            $entityManager->flush();

            return $this->redirectToRoute('index');
            
        }

        return $this->render('forum/newSubject.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/user/subjects", methods={"GET"}, name="userSubjects")
     */
    public function userSubjects(): Response
    {
        $subjects = $this->getUser()->getSubjects();
        return $this->render('forum/index.html.twig', [
            "subjects" => $subjects
        ]);
    }

    /**
     * @Route("/comment/{answerId}", methods={"GET", "POST"}, name="newComment", requirements={"answerId"="\d+"})
     */
    public function newComment(Request $request, AnswerRepository $answerRepository, int $answerId): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $comment->setPublished(new \DateTime());
            $comment->setUser($this->getUser());
            // Il faut récupérer la réponse associée au commentaire car nous n'y avons pas accès sur cette page
            $answer = $answerRepository->find($answerId);
            $comment->setAnswer($answer);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            // Pour la redirection on récupère l'id du sujet via l'entité answer
            return $this->redirectToRoute('single', ["id" => $answer->getSubject()->getId()]);
        }

        return $this->render('forum/newComment.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/user/profil", methods={"GET", "POST"}, name="userProfil")
     */
    public function userProfil(): Response
    {
        // On récupère l'utilisateur connecté avec ses informations
        $user = $this->getUser();
        // Le formulaire sera prérempli avec les informations de l'entité
        $form = $this->createForm(RegistrationFormType::class, $user);
        return $this->render('forum/userProfil.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
