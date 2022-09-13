<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use App\Form\SearchPhotoType;
use App\Repository\PhotoRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class PhotoController extends AbstractController
{
    #[Route('/', name: 'app_photo_index', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): Response
    {
        return $this->render('photo/index.html.twig', [
            'photos' => $photoRepository->findAll()
        ]);
    }

    #[Route('/search', name: 'app_photo_search', methods: ['GET', 'POST'])]
    public function search(PhotoRepository $photoRepository, Request $request): Response
    {
        $photos = new Photo();
        $searchForm = $this->createForm(SearchPhotoType::class, $photos);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData()->getCategory();
            $photos = $photoRepository->searchByCategory($data);
        }

        return $this->render('photo/search.html.twig', [
            'photos' => $photos,
            'searchForm' => $searchForm->createView()
        ]);
    }

    #[Route('/new', name: 'app_photo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PhotoRepository $photoRepository): Response
    {
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photos = $form->get('photos')->getData();
            $category = $form->get('category')->getData();

            // dd($photos);

            foreach ($photos as $photo) {
                $fichier = md5(uniqid()) . '.' . $photo->guessExtension();

                $photo->move(
                    $this->getParameter('photos_directory'),
                    $fichier
                );
                $upload = new Photo();
                $upload->setName($fichier);
                $upload->setCategory($category);
                $upload->setUploadedAt(new DateTimeImmutable());
                $photoRepository->add($upload, true);
            }

            return $this->redirectToRoute('app_photo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('photo/new.html.twig', [
            'photo' => $photo,
            'form' => $form,
        ]);
    }

    // #[Route('/{id}', name: 'app_photo_show', methods: ['GET'])]
    // public function show(Photo $photo): Response
    // {
    //     return $this->render('photo/show.html.twig', [
    //         'photo' => $photo,
    //     ]);
    // }

    // #[Route('/{id}/edit', name: 'app_photo_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Photo $photo, PhotoRepository $photoRepository): Response
    // {
    //     $form = $this->createForm(PhotoType::class, $photo);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $photoRepository->add($photo, true);

    //         return $this->redirectToRoute('app_photo_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('photo/edit.html.twig', [
    //         'photo' => $photo,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_photo_delete', methods: ['POST'])]
    public function delete(Request $request, Photo $photo, PhotoRepository $photoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$photo->getId(), $request->request->get('_token'))) {
            //suppression du fichier correspondant
            unlink($this->getParameter('photos_directory'). '/' . $photo->getName());
            //supression du nom de fichier dans la bdd
            $photoRepository->remove($photo, true);
        }

        return $this->redirectToRoute('app_photo_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
