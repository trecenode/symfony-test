<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/amazon.json';
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        $products = $data['SearchResult']['Items'];

        usort($products, function ($item1, $item2) {
            return $item1['BrowseNodeInfo']['BrowseNodes'][0]['SalesRank'] <=> $item2['BrowseNodeInfo']['BrowseNodes'][0]['SalesRank'];
        });

        $lowestPrice = PHP_INT_MAX;
        $cheapestProductId = null;

        foreach ($products as $key => $product) {
            $currentPrice = $product['Offers']['Listings'][0]['Price']['Amount'] ?? PHP_INT_MAX;
            if ($currentPrice < $lowestPrice) {
                $lowestPrice = $currentPrice;
                $cheapestProductId = $key;
            }
        }

        return $this->render('products/list.html.twig', [
            'products' => $products,
            'cheapestProductId' => $cheapestProductId,
        ]);
    }
}