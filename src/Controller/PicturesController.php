<?php

namespace App\Controller;

use App\Entity\Pictures;
use App\Form\PicturesType;
use App\Repository\PicturesRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/pictures')]
class PicturesController extends AbstractController
{
    #[Route('/', name: 'app_pictures_index', methods: ['GET'])]
    public function index(PicturesRepository $picturesRepository): Response
    {
        return $this->render('pages/pictures/index.html.twig', [
            'pictures' => $picturesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pictures_new', methods: ['GET', 'POST'])]
    public function new(Filesystem $fs, Request $request, PicturesRepository $picturesRepository): Response
    {
        // Controle si l'utilisateur est identifié
        if (!$this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $picture = new Pictures();
        $form = $this->createForm(PicturesType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Copy du fichier image
            // form: repertoire temporaire
            // to: $project_dir/public/uploads/

            // Project directory root
            $root = $this->getParameter('kernel.project_dir');

            // The upload directory
            $uploadsDir = $root . "/public/uploads/";

            // Define source and destination
            $source = $form['source']->getData()->getPathname();

            // NOM METHODE 1
            // Nom du fichier originale
            // --

            // $filename = $form['source']->getData()->getClientOriginalName();
            

            // NOM METHODE 2
            // Generation du nom a partir du contenu du fichier
            // --

            // Recupération de l'extention du fichier original
            $extension = $form['source']->getData()->getClientOriginalName();
            $extension = explode(".", $extension);
            $extension = $extension[1];
            $extension = ".".$extension;

            // Generation du nom a partir du contenu hashé en MD5
            $filename = file_get_contents($source);
            $filename = md5($filename);
            $filename.= $extension;

            $destination = $uploadsDir.$filename;
            
            // Copy temp file to upload dir
            $fs->copy(
                $source,
                $destination,
                true
            );

            // 3. Modifier la source du fichier $picture->setSource()
            $picture->setSource( $filename );

            // Association de l'entité Usert à l'entité Picture
            $picture->setUser( $this->getUser() );


            $picturesRepository->save($picture, true);

            return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pages/pictures/new.html.twig', [
            'picture' => $picture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pictures_show', methods: ['GET'])]
    public function show(Pictures $picture): Response
    {
        return $this->render('pages/pictures/show.html.twig', [
            'picture' => $picture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pictures_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pictures $picture, PicturesRepository $picturesRepository): Response
    {
        $form = $this->createForm(PicturesType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picturesRepository->save($picture, true);

            return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pages/pictures/edit.html.twig', [
            'picture' => $picture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pictures_delete', methods: ['POST'])]
    public function delete(Request $request, Pictures $picture, PicturesRepository $picturesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $request->request->get('_token'))) {
            $picturesRepository->remove($picture, true);
        }

        return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
    }
}
