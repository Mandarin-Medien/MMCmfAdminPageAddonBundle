<?php

namespace MandarinMedien\MMCmf\Admin\PageAddonBundle\Form;

use Doctrine\ORM\EntityRepository;
use MandarinMedien\MMCmfContentBundle\Form\Type\TemplatableNodeTemplateType;
use MandarinMedien\MMCmfContentBundle\Entity\Page;
use MandarinMedien\MMCmfContentBundle\Form\FormTypeMetaReader;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;


class PageType extends AbstractType
{

    protected $container;
    protected $hiddenFields = array('id');

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // get class of the current entity for template selection
        $class = get_class($options['data']);


        // get class meta data
        $manager = $this->container->get('doctrine')->getManager();
        $formTypeReader = new FormTypeMetaReader();

        $metaData = $manager->getClassMetadata($class);

        // loop default fields
        foreach ($metaData->getFieldNames() as $field) {
            if (in_array($field, $this->hiddenFields)) continue;


            $builder->add($field, $formTypeReader->get($class, $field));

        }

        // loop association fields
        foreach ($metaData->getAssociationNames() as $field) {

            if (in_array($field, array(
                'parent',
                'nodes',
                'routes',
                'template'
            ))) continue;

            $builder->add($field, $formTypeReader->get($class, $field));
        }


        /**
         * @var Router $router
         */
        $router = $this->container->get('router');

        $builder
            ->add('parent', EntityType::class, array(
                'class' => Page::class,
                'required' => false,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('p');
                }
            ))
            ->add('template', TemplatableNodeTemplateType::class, array('className' => $class ))

            ->add('submit', SubmitType::class, array('label' => 'save'))
            ->add('save_and_add', SubmitType::class, array(
                'attr' => array(
                    'data-target' => $router->generate('mm_cmf_admin_page_addon_page_new')
                )
            ))
            ->add('save_and_back', SubmitType::class, array(
                'attr' => array(
                    'data-target' => $router->generate('mm_cmf_admin_page_addon_page')
                )
            ));
    }
}
