<?php

namespace App\Controller\Admin;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

use App\Entity\Movie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class MovieCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }
    
    public function createEntity(string $entityFqcn)
    {
        $movie = new Movie();
        $movie->setOwner($this->getUser());

        return $movie;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Movie Vote')
            ->setEntityLabelInPlural('Movie Votes')
            ->setSearchFields(['title', 'description'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('conference'));
    }
    
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('movie');
        yield TextField::new('title');
        yield TextareaField::new('description')->hideOnIndex();
        yield ImageField::new('photoPath')->setBasePath('/uploads/photos')->setLabel('Photo')->onlyOnIndex();

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'html5' => true,
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        } else {
            yield $createdAt;
        }
    }    
}
