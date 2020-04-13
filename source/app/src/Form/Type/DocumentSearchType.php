<?php

namespace App\Form\Type;

use App\Repository\DocumentRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class DocumentSearchType extends AbstractType
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @param DocumentRepository $repository
     */
    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aggregations = $this->repository->getAggregates();

        $yearMin = $aggregations->getAggregations()['created_min']->getAsDateTime()->format('Y');
        $yearMax = $aggregations->getAggregations()['created_max']->getAsDateTime()->format('Y');

        $builder
            ->add('query', TextType::class, [
                'label' => 'Zoektermen',
                'constraints' => [
                    new Length(['min' => 1, 'max' => 255])
                ],
                'required' => false
            ])
            ->add('sender', TextType::class, [
                'label' => 'Afzender',
                'constraints' => [
                    new Length(['min' => 1, 'max' => 255])
                ],
                'required' => false
            ])
            ->add('recipient', ChoiceType::class, [
                'label' => 'Geadresseerde',
                'choices' => $aggregations->getAggregations()['recipient']->getBucketKeys(),
                'empty_data' => '',
                'required' => false
            ])
            ->add('created_min', DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
                'years' => range($yearMin, $yearMax),
                'label' => 'Van',
                'required' => false
            ])
            ->add('created_max', DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
                'years' => range($yearMin, $yearMax),
                'label' => 'Tot',
                'required' => false
            ])
            ->add('sort', ChoiceType::class, [
                'label' => 'Sortering',
                'choices' => [
                    'Relevantie' => '',
                    'Datum (Nieuw -> Oud)' => 'created_desc',
                    'Datum (Oud -> Nieuw)' => 'created_asc',
                ],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('zoeken', SubmitType::class);
    }
}
