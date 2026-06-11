<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $responses = [
        'bonjour' => 'Bonjour! Comment puis-je vous aider aujourd\'hui?',
        'salut' => 'Salut! Que puis-je faire pour vous?',
        'aide' => 'Je peux vous aider à chercher des médicaments, trouver des pharmacies, suivre vos commandes, ou répondre à vos questions sur la plateforme DwakFJib.',
        'medicament' => 'Utilisez la barre de recherche pour trouver vos médicaments par nom ou catégorie. Vous pouvez aussi filtrer par pharmacie.',
        'pharmacie' => 'Vous pouvez trouver les pharmacies près de chez vous sur la page des pharmacies. Utilisez la carte interactive pour les localiser.',
        'commande' => 'Pour suivre votre commande, allez dans la section Commandes de votre tableau de bord.',
        'reservation' => 'Vous pouvez réserver des médicaments et suivre vos réservations depuis votre tableau de bord.',
        'paiement' => 'Les paiements se font en MAD (Dirhams Marocains). Nous acceptons les cartes bancaires et le paiement à la livraison.',
        'livraison' => 'La livraison est assurée par nos distributeurs partenaires. Vous pouvez suivre votre livraison en temps réel.',
        'compte' => 'Vous pouvez gérer votre profil, mot de passe et préférences depuis les paramètres de votre compte.',
        'contact' => 'Pour toute assistance, contactez notre support au +212 5 22 00 00 00 ou par email à support@dwakfjib.ma',
        'merci' => 'De rien! N\'hésitez pas si vous avez d\'autres questions.',
    ];

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = strtolower(trim($request->message));
        $user = Auth::user();

        $response = $this->generateResponse($message, $user);

        return response()->json([
            'message' => $response['text'],
            'suggestions' => $response['suggestions'],
            'data' => $response['data'] ?? null,
        ]);
    }

    protected function generateResponse(string $message, $user): array
    {
        $suggestions = $this->getSuggestions();

        if ($user && $user->isPharmacy()) {
            return $this->pharmacyResponse($message);
        }

        if ($user && $user->isDistributor()) {
            return $this->distributorResponse($message);
        }

        return $this->generalResponse($message, $suggestions);
    }

    protected function pharmacyResponse(string $message): array
    {
        $pharmacy = Auth::user()->pharmacy;

        if (str_contains($message, 'stock') || str_contains($message, 'inventaire')) {
            $lowStock = Medication::where('pharmacy_id', $pharmacy->id)
                ->whereColumn('stock', '<=', 'low_stock_threshold')
                ->count();
            $total = Medication::where('pharmacy_id', $pharmacy->id)->count();

            return [
                'text' => "Votre inventaire: $total médicaments, dont $lowStock en alerte de stock bas.",
                'suggestions' => ['Voir mon inventaire', 'Ajouter un médicament', 'Alertes stock'],
                'data' => ['type' => 'stock', 'total' => $total, 'low_stock' => $lowStock],
            ];
        }

        if (str_contains($message, 'patient') || str_contains($message, 'fidèle') || str_contains($message, 'fidél')) {
            $top = \App\Models\LoyalPatient::where('pharmacy_id', $pharmacy->id)
                ->orderBy('total_spent', 'desc')
                ->limit(5)
                ->get();

            return [
                'text' => "Vous avez " . $top->count() . " patients fidèles. Les meilleurs: " . $top->pluck('user.name')->implode(', '),
                'suggestions' => ['Voir tous les patients', 'Ajouter un médicament', 'Statistiques'],
                'data' => ['type' => 'patients', 'patients' => $top],
            ];
        }

        if (str_contains($message, 'vente') || str_contains($message, 'chiffre')) {
            $reservations = Reservation::where('pharmacy_id', $pharmacy->id)->count();

            return [
                'text' => "Vous avez $reservations réservations au total. Accédez à vos statistiques pour plus de détails.",
                'suggestions' => ['Voir les ventes', 'Analytiques', 'Rapports'],
                'data' => ['type' => 'sales', 'reservations' => $reservations],
            ];
        }

        return [
            'text' => 'Bonjour pharmacien! Je suis votre assistant DwakFJib. Je peux vous aider à gérer votre stock, voir vos patients fidèles, ou suivre vos ventes.',
            'suggestions' => ['Mon inventaire', 'Patients fidèles', 'Ajouter médicament', 'Alertes stock'],
        ];
    }

    protected function distributorResponse(string $message): array
    {
        if (str_contains($message, 'commande')) {
            $orders = Order::where('distributor_id', Auth::user()->distributor->id)->count();

            return [
                'text' => "Vous avez $orders commandes au total.",
                'suggestions' => ['Voir les commandes', 'Livraisons en cours', 'Statistiques'],
                'data' => ['type' => 'orders', 'total' => $orders],
            ];
        }

        return [
            'text' => 'Bonjour distributeur! Je suis votre assistant DwakFJib. Je peux vous aider à gérer vos commandes et livraisons.',
            'suggestions' => ['Mes commandes', 'Livraisons', 'Partenaires', 'Statistiques'],
        ];
    }

    protected function generalResponse(string $message, array $defaultSuggestions): array
    {
        foreach ($this->responses as $key => $response) {
            if (str_contains($message, $key)) {
                return [
                    'text' => $response,
                    'suggestions' => $defaultSuggestions,
                ];
            }
        }

        if (str_contains($message, 'prix') || str_contains($message, 'cout') || str_contains($message, 'tarif')) {
            return [
                'text' => 'Les prix sont affichés en MAD (Dirhams Marocains) sur chaque produit. Vous pouvez comparer les prix entre différentes pharmacies.',
                'suggestions' => $defaultSuggestions,
            ];
        }

        if (str_contains($message, 'urgent') || str_contains($message, 'garde')) {
            return [
                'text' => 'Pour les urgences, utilisez la section "Pharmacies de garde" pour trouver une pharmacie ouverte près de chez vous.',
                'suggestions' => $defaultSuggestions,
            ];
        }

        return [
            'text' => 'Je n\'ai pas bien compris votre demande. Pouvez-vous reformuler? Voici quelques sujets sur lesquels je peux vous aider.',
            'suggestions' => $defaultSuggestions,
        ];
    }

    protected function getSuggestions(): array
    {
        return [
            'Chercher un médicament',
            'Trouver une pharmacie',
            'Comment passer commande?',
            'Suivi de livraison',
            'Contact support',
        ];
    }
}
