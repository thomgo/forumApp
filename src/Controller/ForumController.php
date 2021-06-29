<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subject;
use App\Form\SubjectType;
use App\Repository\SubjectRepository;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        // On récupère le repo (le manger/model) de l'entité Subject, ce repo contient déjà des requêtes simples en BDD
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        // Sur le repo on appelle la méthode findAll qui renvoie toutes les entités (ici Subject)
        $subjects = $subjectRepository->findAll();
        // On retourne une vue sous forme de réponse et on lui passe une variables subjects à laquelle on associe $subjects
        return $this->render('forum/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/forum/subject/{id}", name="single", requirements={"id"="\d+"})
     */
    // Méthode pour afficher un sujet. Elle attend un paramètre id car la route attend un paramètre
    // On précise dans la route et la méthode que ce paramètre est un integer
    public function single(int $id=1, SubjectRepository $subjectRepository): Response
    {
        // Contrairement à l'index ici le repo de l'entité à été passé directement en paramètre
        // On fait appelle à la méthode find du repo qui recherche une entité par sa clef primaire
        $subject = $subjectRepository->find($id);

        return $this->render('forum/single.html.twig', [
            "subject" => $subject
        ]);
    }
    
    /**
     * @Route("/forum/rules", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('forum/rules.html.twig', [
        ]);
    }

    /**
     * @Route("/forum/subject/new", name="newSubject")
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
}
