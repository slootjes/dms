<?php

namespace App\Controller;

use App\Form\Type\DocumentSearchType;
use App\Repository\DocumentRepository;
use App\VO\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class SearchController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $path;

    /**
     * @param Environment $twig
     * @param DocumentRepository $repository
     * @param string $documentPath
     */
    public function __construct(Environment $twig, DocumentRepository $repository, string $documentPath)
    {
        $this->twig = $twig;
        $this->repository = $repository;
        $this->path = $documentPath;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $result = null;
        $form = $this->createForm(DocumentSearchType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->repository->search($form->getData(), Document::class);
        }

        return $this->render(
            'search/index.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result
            ]
        );
    }

    /**
     * @Route("/view/{id}", name="view")
     *
     * @param string $id
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function view(string $id): BinaryFileResponse
    {
        return $this->getFile($id, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/download/{id}", name="download")
     *
     * @param string $id
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download(string $id): BinaryFileResponse
    {
        return $this->getFile($id);
    }

    /**
     * @param string $id
     * @param string $disposition
     * @return BinaryFileResponse
     * @throws \Exception
     */
    private function getFile(string $id, $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $document = $this->repository->findById($id);
        $filePath = $this->path . $document->getFilepathDownload();
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('Could not load file at path: %s', $filePath));
        }

        return $this->file($filePath, $document->getFileName(), $disposition);
    }
}
