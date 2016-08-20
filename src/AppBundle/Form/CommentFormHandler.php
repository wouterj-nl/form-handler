<?php

namespace AppBundle\Form;

use Hostnet\Component\Form\AbstractFormHandler;
use Hostnet\Component\Form\FormSuccesHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Comment;

// Handlers must implement FormHandlerInterface, but extending the
// AbstractFormHandler makes things much easier.
class CommentFormHandler extends AbstractFormHandler
{
    /** @var Post **/
    private $post;
    /** @var ObjectManager **/
    private $entityManager;
    /** @var TokenStorageInterface **/
    private $tokenStorage;

    public function __construct(ObjectManager $manager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $manager;
        $this->tokenStorage  = $tokenStorage;
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
    }
    
    public function onSuccess(Request $request)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        // get the form data (Comment entity) after success
        /** @var Comment $comment */
        $comment = $this->getForm()->getData();
        $comment->setAuthorEmail($currentUser->getEmail());
        $comment->setPost($this->post);

        // persist the new Comment entity
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);

        // this will be returned to the controller
        return true;
    }

    public function getData()
    {
        // returns the initial form data (e.g. a new entity).
        // As the CommentType constructs the initial data itself using the
        // data_class option, we don't return anything here
        return null;
    }

    public function getType()
    {
        // the FQCN of the form type related to this handler
        return CommentType::class;
    }
}
