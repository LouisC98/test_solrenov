<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
    #[Route('/galerie', name: 'galerie')]
    public function galerie(): Response
    {
        return $this->render('galerie/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }


    // Routes pour récupérer les résultats à utiliser avec vuejs
    #[Route('/galerie/all', name: 'all_photos')]
    public function allPhotos(PhotoRepository $photoRepository)
    {   
        $photos = $photoRepository->allPhotosJson();

        return $this->json(json_encode($photos));
    }

    #[Route('/galerie/{id}', name: 'photos_by_category')]
    public function photosByCategory(string $id, PhotoRepository $photoRepository): JsonResponse
    {   
        $photos = $photoRepository->searchByCategoryJson($id);

        return $this->json(json_encode($photos));
    }

    #[Route('/categories', name: 'all_categories')]
    public function allCategories(CategoryRepository $categoryRepository): JsonResponse
    {   
        $categories = $categoryRepository->findAllCategoryName();

        return $this->json(json_encode($categories));
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('about/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $contactForm = $this->createForm(ContactType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $username = $contactForm->get('name')->getData();
            $userEmail = $contactForm->get('email')->getData();
            $objectMsg = $contactForm->get('subject')->getData();
            $message = $contactForm->get('message')->getData();

            $email = (new TemplatedEmail())
                    ->from($userEmail)
                    ->to('louis@gmail.com')
                    ->subject($objectMsg)
                    ->htmlTemplate('email/email.html.twig')
                    ->context([
                        'message' => $message,
                        'name' => $username,
                        'userEmail' => $userEmail
                    ])
            ;
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'Une erreur est survenue');
            }
        }

        return $this->render('contact/index.html.twig', [
            'controller_name' => 'DefaultController',
            'contactForm' => $contactForm->createView()
        ]);
    }
}
