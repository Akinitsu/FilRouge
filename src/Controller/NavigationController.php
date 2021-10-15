<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MessageRepository;
use App\Form\MessageType;

class NavigationController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(MessageRepository $repo): Response
    {
        
        $conversation = $repo->findAll();

        return $this->render('navigation/index.html.twig', [
            'conversations' => $conversation
        ]);
    }

    /**
     * @Route("/message/{id}", name="message_show")
     */
    public function show(Message $message): Response
    {

        return $this->render('navigation/show.html.twig', [
            'messages' => $message
        ]);
    }

    /**
     * @Route("/message/new", name="new_message")
     * @Route("/message/{id}/edit, name="edit_message")
     */
    public function message(Request $request, EntityManagerInterface $manager,Message $message = null): Response
    {
        if(!$message){
            $message = new Message(); //On prends en modele la class Message
        }
        $form = $this->createForm(MessageType::class, $message); //On créer un Formulaire qui ce base sur le message

        $form->handleRequest($request); //On envoie la requete
        if ($form->isSubmitted() && $form->isValid()) { //Si il est envoyé et qu'il est valide alors, 
            if(!$message->getId()){
                $message->setCreatedAt(new \Datetime);
            }
        

            $manager->persist($message); // On prepare a l'envoyé en BDD
            $manager->flush();  // On envoie en BDD

             return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
        }

        return $this->render('navigation/message.html.twig', [
            'formMessage' => $form->createView(),
            'editMode' => $message->getId() !== null,
        ]);
    }
    
    /**
     * @Route("/{id}", name="message_delete", methods={"POST"})
     */
    public function delete(Request $request, Message $message): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($message); //Supprime le message choisi
            $entityManager->flush();//La demande de delete est alors envoyé en BDD pour la supprimer définitivement 
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER); // Et redirige a l'accueil (route 'home')
    }
}