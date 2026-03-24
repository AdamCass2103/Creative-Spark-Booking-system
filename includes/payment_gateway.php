<?php
// ============================================
// PAYMENT GATEWAY INTERFACE
// ============================================

require_once __DIR__ . '/payment_config.php';

// This is where you'll plug in Zero payment system later
// For now, we'll create a manual/offline payment system

class PaymentGateway {
    
    // This will be replaced with Zero API when ready
    public function processPayment($payment_id, $user_id, $amount, $payment_method = 'manual') {
        global $conn;
        
        // Get payment record
        $payment = $conn->query("SELECT * FROM payments WHERE id = $payment_id")->fetch_assoc();
        
        if (PAYMENT_PROCESSOR == 'zero') {
            // FUTURE: Integrate with Zero payment system
            // This is where you'll add Zero API calls
            // return $this->processWithZero($payment_id, $user_id, $amount);
            return [
                'success' => false,
                'message' => 'Zero payment integration pending. Please use manual payment for now.',
                'redirect_url' => null
            ];
        } else {
            // Manual payment mode (admin marks as paid)
            return [
                'success' => true,
                'message' => 'Payment recorded. Please complete payment at the front desk.',
                'manual' => true,
                'payment_id' => $payment_id,
                'amount' => $amount
            ];
        }
    }
    
    // Template for Zero integration (to be completed later)
    private function processWithZero($payment_id, $user_id, $amount) {
        // TODO: Integrate with Zero payment system
        // This is where the actual money transfer happens
        // Zero API endpoint: [to be provided]
        // Zero API key: [to be provided]
        
        /*
        Example structure for Zero integration:
        
        $zero_api_url = getenv('ZERO_API_URL');
        $zero_api_key = getenv('ZERO_API_KEY');
        
        $data = [
            'amount' => $amount,
            'currency' => CURRENCY,
            'payment_id' => $payment_id,
            'user_id' => $user_id,
            'description' => 'Membership Payment - Invoice #' . $payment['invoice_number']
        ];
        
        $ch = curl_init($zero_api_url . '/payments');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $zero_api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'redirect_url' => $result['redirect_url']
            ];
        }
        
        return ['success' => false, 'message' => 'Payment processing failed'];
        */
        
        return ['success' => false, 'message' => 'Zero integration pending'];
    }
    
    // Generate receipt PDF (placeholder - will create actual PDF later)
    public function generateReceipt($payment_id) {
        global $conn;
        
        $payment = $conn->query("SELECT p.*, u.name, u.email FROM payments p 
                                 JOIN users u ON p.user_id = u.user_id 
                                 WHERE p.id = $payment_id")->fetch_assoc();
        
        if (!$payment) return false;
        
        // Create HTML receipt
        $receipt_html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .receipt { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                .header { text-align: center; border-bottom: 2px solid #2E7D32; padding-bottom: 20px; }
                .amount { font-size: 24px; color: #2E7D32; font-weight: bold; }
                .details { margin: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='receipt'>
                <div class='header'>
                    <h1>Creative Spark FabLab</h1>
                    <p>Payment Receipt</p>
                </div>
                <div class='details'>
                    <p><strong>Invoice #:</strong> {$payment['invoice_number']}</p>
                    <p><strong>Date:</strong> " . date('F j, Y', strtotime($payment['created_at'])) . "</p>
                    <p><strong>Member:</strong> {$payment['name']}</p>
                    <p><strong>Email:</strong> {$payment['email']}</p>
                    <hr>
                    <p><strong>Subtotal:</strong> €" . number_format($payment['amount'], 2) . "</p>
                    <p><strong>VAT (23%):</strong> €" . number_format($payment['tax'], 2) . "</p>
                    <p class='amount'><strong>Total Paid:</strong> €" . number_format($payment['total'], 2) . "</p>
                    <p><strong>Payment Type:</strong> " . ucfirst($payment['payment_type']) . "</p>
                    <p><strong>Status:</strong> " . ucfirst($payment['status']) . "</p>
                </div>
                <div class='footer'>
                    <p>Creative Spark FabLab, Dundalk, Co. Louth</p>
                    <p>info@creativespark.ie | www.creativespark.ie</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Save receipt
        $receipt_path = __DIR__ . '/../receipts/receipt_' . $payment['invoice_number'] . '.html';
        file_put_contents($receipt_path, $receipt_html);
        
        // Update invoice record
        $conn->query("INSERT INTO invoices (payment_id, invoice_number, pdf_path) 
                      VALUES ($payment_id, '{$payment['invoice_number']}', '$receipt_path')");
        
        return $receipt_path;
    }
}

// Initialize payment gateway
$payment_gateway = new PaymentGateway();
?>