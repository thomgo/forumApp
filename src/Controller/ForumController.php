<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subject;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        $subjects = $subjectRepository->findAll();

        return $this->render('forum/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/forum/rules", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('forum/rules.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }
}
