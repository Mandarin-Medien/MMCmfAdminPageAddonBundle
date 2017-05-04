<?php

namespace MandarinMedien\MMCmf\Admin\PageAddonBundle\Controller;

use MandarinMedien\MMAdminBundle\Controller\BaseController;
use MandarinMedien\MMAdminBundle\Frontend\Widget\Admin\AdminListWidget;
use MandarinMedien\MMCmf\Admin\PageAddonBundle\Form\PageType;
use MandarinMedien\MMCmfContentBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PageAdminController
 * @package MandarinMedien\MMCmf\Admin\PageAddonBundle\Controller
 */
class PageAdminController extends BaseController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $userList = new AdminListWidget($this->container, Page::class);
        $userList
            ->add('name')
            ->add('position')
            ->add('template')
            ->addEditAction('mm_cmf_admin_page_addon_page_edit')
            ->addDeleteAction('mm_cmf_admin_page_addon_page_delete')
            ->addAction('add', [
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn-default'],
                'url' => function (RouterInterface $router) {
                    return $router->generate('mm_cmf_admin_page_addon_page_new');
                }
            ]);
        $userList->addParameter('pageHeadline','Pagemanagement Section');

        return $this->renderWidget($userList);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function newAction(Request $request)
    {
        $entity = new Page();
        $form   = $this->createCreateForm($entity);

        return $this->render(
            'MMCmfAdminPageAddonBundle:Page:new.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );

    }


    /**
     * @param Request $request
     * @return \MandarinMedien\MMAdminBundle\Controller\JsonFormResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $entity = new Page();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
        }

        return $this->formResponse($form);
    }


    /**
     * @param Page $entity
     * @return \Symfony\Component\Form\Form
     */
    private function createCreateForm(Page $entity)
    {
        $form = $this->createForm(PageType::class, $entity, array(
            'action' => $this->generateUrl('mm_cmf_admin_page_addon_page_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }


    /**
     * Displays a form to edit an existing Page entity.
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MMCmfContentBundle:Page')->findOneBy(array('id'=>$id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        $editForm = $this->createEditForm($entity);


        return $this->render(
            'MMCmfAdminPageAddonBundle:Page:edit.html.twig',
            array(
                'entity' => $entity,
                'form'   => $editForm->createView(),
            )
        );
    }


    /**
     * @param Page $entity
     * @return \Symfony\Component\Form\Form
     */
    private function createEditForm(Page $entity)
    {
        $form = $this->createForm(PageType::class, $entity, array(
            'action' => $this->generateUrl('mm_cmf_admin_page_addon_page_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr' => array(
                'rel' => 'ajax'
            )
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }


    /**
     * @param Request $request
     * @param $id
     * @return \MandarinMedien\MMAdminBundle\Controller\JsonFormResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MMCmfContentBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }


        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
        }

        return $this->formResponse($editForm);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {


        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        //if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MMCmfContentBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        if($parent = $entity->getParent())
        {
            $parent->removeNode($entity);
        }

        $em->remove($entity);
        $em->flush();
        //}

        return $this->redirect($this->generateUrl('mm_cmf_admin_page_addon_page'));
    }


    /**
     * @param $id
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('mm_cmf_admin_page_addon_page_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
            ;
    }
}
