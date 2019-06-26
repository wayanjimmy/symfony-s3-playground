<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    public function new(Request $request, FileUploader $fileUploader)
    {
        $task = new Task();
        $task->setTask('Write a blog post');

        $form = $this
            ->createFormBuilder($task)
            ->add('task', TextType::class)
            ->add('media', FileType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Task $task */
            $task = $form->getData();

            $fileUploader->upload($task->getMedia());
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function rename(FileUploader $fileUploader)
    {
        $result = $fileUploader->rename('document.pdf', 'boundjobs/jimmy2/document.pdf');

        dump(compact('result'));

        return new Response("rename me!");
    }
}
