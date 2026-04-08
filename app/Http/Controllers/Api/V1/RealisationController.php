<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RealisationResource;
use App\Models\Realisation;
use Illuminate\Http\Request;

class RealisationController extends Controller
{
    /**
     * Liste des réalisations publiées (paginée)
     * GET /api/v1/realisations
     *
     * Paramètres query:
     *  - per_page (int, default 12)
     *  - categorie (string, optionnel)
     *  - search (string, optionnel)
     *  - date_from (date, optionnel)
     *  - date_to (date, optionnel)
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 12), 50);

        $query = Realisation::published()
            ->when($request->filled('categorie'), function ($q) use ($request) {
                $q->where('categorie', $request->categorie);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('titre', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%")
                       ->orWhere('lieu', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('date_realisation', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('date_realisation', '<=', $request->date_to);
            })
            ->orderBy('date_realisation', 'desc');

        $realisations = $query->paginate($perPage);

        return RealisationResource::collection($realisations);
    }

    /**
     * Détail d'une réalisation publiée
     * GET /api/v1/realisations/{id}
     */
    public function show(int $id)
    {
        $realisation = Realisation::published()->findOrFail($id);

        return new RealisationResource($realisation);
    }

    /**
     * Liste des catégories disponibles
     * GET /api/v1/realisations/categories
     */
    public function categories()
    {
        return response()->json([
            'data' => collect(Realisation::getCategories())->map(function ($label, $key) {
                return ['value' => $key, 'label' => $label];
            })->values(),
        ]);
    }

    /**
     * Dernières réalisations (pour widget homepage)
     * GET /api/v1/realisations/latest
     *
     * Paramètres query:
     *  - limit (int, default 6, max 20)
     */
    public function latest(Request $request)
    {
        $limit = min((int) $request->get('limit', 6), 20);

        $realisations = Realisation::published()
            ->orderBy('date_realisation', 'desc')
            ->limit($limit)
            ->get();

        return RealisationResource::collection($realisations);
    }
}

