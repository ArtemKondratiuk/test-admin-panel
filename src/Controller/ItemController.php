<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Service\ItemServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemController extends AbstractController
{
    public function __construct(
        private ItemServiceInterface $itemService
    ) {
    }

    #[Route('/', name: 'app_item_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = ItemServiceInterface::ITEMS_PER_PAGE;

        $totalCount = $this->itemService->getTotalCount();
        $lastPage = (int) ceil($totalCount / $limit);

        $pagination = $this->itemService->getPaginatedItems($page);

        if ($page > $lastPage && $lastPage > 0) {
            return $this->redirectToRoute('app_item_index', ['page' => $lastPage]);
        }

        return $this->render('item/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    #[Route('/new', name: 'app_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->itemService->createItem($item);

            return $this->redirectToRoute('app_item_index');
        }

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->itemService->createItem($item);
            return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item): Response
    {
        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
            $this->itemService->deleteItem($item);
        }

        return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/move/{direction}', name: 'app_item_move', methods: ['POST'])]
    public function move(Item $item, string $direction): Response
    {
        $this->itemService->moveItem($item, $direction);
        $limit = ItemServiceInterface::ITEMS_PER_PAGE;
        $newPosition = $item->getPosition();
        $targetPage = (int) ceil($newPosition / $limit);

        return $this->redirectToRoute('app_item_index', [
            'page' => $targetPage
        ]);
    }
}
