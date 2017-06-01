<?php

namespace MandarinMedien\MMCmf\Admin\PageAddonBundle\Controller;

use MandarinMedien\MMAdminBundle\Controller\AdminController;
use MandarinMedien\MMAdminBundle\Form\Group\BoxType;
use MandarinMedien\MMAdminBundle\Form\Group\LinkType;
use MandarinMedien\MMAdminBundle\Frontend\Widget\Admin\AdminListWidget;
use MandarinMedien\MMCmf\Admin\PageAddonBundle\Form\Group\PageRouteWidgetGroupType;
use MandarinMedien\MMCmf\Admin\PageAddonBundle\Form\PageType;
use MandarinMedien\MMCmfContentBundle\Entity\Page;
use MandarinMedien\MMFormGroupBundle\Group\Type\GroupType;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Tests\Fixtures\ChoiceSubType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PageAdminController
 * @package MandarinMedien\MMCmf\Admin\PageAddonBundle\Controller
 */
class PageAdminController extends AdminController
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
        $userList->addParameter('pageHeadline', 'Pagemanagement Section');

        return $this->renderWidget($userList);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function newAction(Request $request)
    {
        $entity = new Page();
        $form = $this->createCreateForm($entity);

        return $this->render(
            'MMCmfAdminPageAddonBundle:Page:new.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );

    }

    /**
     * checks if the MMCmf/Admin/RoutingAddonBundle is installed to show further configurations
     *
     * @return bool|null
     */
    protected function isRoutingAddonEnabled()
    {
        static $routingAddonIsEnabled = null;

        if ($routingAddonIsEnabled === null) {

            $routingAddonIsEnabled = array_key_exists(
                'MMCmfAdminRoutingAddonBundle',
                $this->container->getParameter('kernel.bundles')
            );
        }
        return $routingAddonIsEnabled;

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
        $form->add('save_and_add', SubmitType::class, array('label' => 'Save and Add'));
        $form->add('save_and_back', SubmitType::class, array('label' => 'Save and Back'));

        return $form;
    }


    /**
     * Displays a form to edit an existing Page entity.
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Page::class)->findOneBy(array('id' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        /**
         * @var Router $router
         */
        $router = $this->get('router');

        $formTypeGroup = $this->createFormGroupBuilder(PageType::class, $entity, array(
            'action' => $this->generateUrl('mm_cmf_admin_page_addon_page_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr' => array(
                'rel' => 'ajax'
            )
        ));

        $formTypeGroup
            ->add('left',GroupType::class, ['attr' => ['class'=>'col-xs-12 col-sm-6']])

            ->add('left-row',GroupType::class, ['attr' => ['class'=>'row']])
                ->add('core', BoxType::class, ['title'=>'Stammdaten', 'attr' => ['class'=>'col-xs-12 col-md-12']])
                    ->add('visible')->end()
                    ->add('name')->end()
                    ->add('position')->end()

                    ->add('template')->end()
                    ->add('parent')->end()

                ->end()
                ->add('metadata', BoxType::class, ['title'=>'Meta-Daten','attr' => ['class'=>'col-xs-12 col-sm-12']])
                    ->add('metaTitle')->end()
                    ->add('metaImage')->end()
                    ->add('metaKeywords')->end()
                    ->add('metaDescription')->end()
                    ->add('metaRobots',ChoiceType::class, [
                        'choices' => [
                            'index,follow' => 'index,follow',
                            'noindex,follow'=>'noindex,follow',
                            'index,nofollow'=>'index,nofollow',
                            'noindex,nofollow'=>'noindex,nofollow',
                        ]
                    ])->end()
                    ->add('metaAuthor')->end()
                ->end()
            ->end()
        ->end()
        ;

        /**
         * routing widget
         */
        if($this->isRoutingAddonEnabled())
            $formTypeGroup->add('routes', PageRouteWidgetGroupType::class, ['attr' => ['class'=>'col-xs-12 col-sm-6']])->end();

        /**
         * form actions
         */
        $formTypeGroup
            ->add('actions', BoxType::class, ['attr' => ['class'=>'col-xs-12']])

                ->add('submit', SubmitType::class, array(
                    'label' => 'save',
                    'attr' => [
                        'class'=>'pull-left  btn-primary'
                    ]))->end()

                ->add('save_and_add', SubmitType::class, array(
                    'attr' => array(
                        'class'=>'pull-left btn-primary',
                        'data-target' => $router->generate('mm_cmf_admin_page_addon_page_new')
                    )
                ))->end()

                ->add('save_and_back', SubmitType::class, array(
                    'attr' => array(
                        'class'=>'pull-left btn-primary',
                        'data-target' => $router->generate('mm_cmf_admin_page_addon_page')
                    )
                ))->end()

                ->add('back',LinkType::class, [
                    'attr' => array('class'=>'pull-right'),
                    'title' => 'back',
                    'href'=> $router->generate('mm_cmf_admin_page_addon_page')])->end()
            ->end();



        return $this->render(
            'MMCmfAdminPageAddonBundle:Page:edit.html.twig',
            array(
                'entity' => $entity,
                'form' => $formTypeGroup->getGroup()->createView(),
                'isRoutingAddonEnabled' => $this->isRoutingAddonEnabled()
            )
        );
    }


    /**
     * @deprecated
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

        if ($parent = $entity->getParent()) {
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
            ->getForm();
    }
}
