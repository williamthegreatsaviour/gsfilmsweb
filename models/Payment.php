<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/config/Config.php';

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO payments (user_id, movie_id, rental_id, amount, currency, stripe_payment_intent_id, stripe_session_id, payment_type, status, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['user_id'],
            $data['movie_id'] ?? null,
            $data['rental_id'] ?? null,
            $data['amount'],
            $data['currency'] ?? 'usd',
            $data['stripe_payment_intent_id'] ?? null,
            $data['stripe_session_id'] ?? null,
            $data['payment_type'] ?? 'rental',
            $data['status'] ?? 'pending',
            $data['metadata'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateStatus($id, $status, $stripePaymentIntentId = null) {
        $sql = "UPDATE payments SET status = ?";
        $params = [$status];
        
        if ($stripePaymentIntentId) {
            $sql .= ", stripe_payment_intent_id = ?";
            $params[] = $stripePaymentIntentId;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id) {
        return $this->db->fetchOne("SELECT * FROM payments WHERE id = ?", [$id]);
    }
    
    public function findByStripeSession($sessionId) {
        return $this->db->fetchOne("SELECT * FROM payments WHERE stripe_session_id = ?", [$sessionId]);
    }
    
    public function findByStripePaymentIntent($paymentIntentId) {
        return $this->db->fetchOne("SELECT * FROM payments WHERE stripe_payment_intent_id = ?", [$paymentIntentId]);
    }
    
    public function getUserPayments($userId) {
        return $this->db->fetchAll("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    }
    
    public function createStripeSession($userId, $movie, $baseUrl) {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        \Stripe\Stripe::setApiKey(Config::STRIPE_SECRET_KEY);
        
        $movieTitle = $movie['title'];
        $price = $movie['rental_price'] * 100;
        
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Renta: {$movieTitle}",
                            'description' => 'Acceso por 48 horas',
                        ],
                        'unit_amount' => (int)$price,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => $baseUrl . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $baseUrl . '/payment/cancel',
            'metadata' => [
                'user_id' => $userId,
                'movie_id' => $movie['id'],
                'type' => 'rental'
            ]
        ]);
        
        return $session;
    }
}
