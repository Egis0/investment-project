<?php

class InvestmentController
{
    public function __construct(private readonly array $rates)
    {
    }
    
    public function processRequest(string $method): void
    {
        switch ($method) {
            case 'POST':
                $data = (array) json_decode(file_get_contents('php://input'), true);
                
                $errors = $this->validate($data);
                
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(['errors' => $errors]);
                    break;
                }

                $payments_data = $this->get_payments((int)$data['sum']);
                if ($payments_data) {
                    http_response_code(201);
                    echo json_encode([
                        'message' => 'Mokėjimų grafikas sugeneruotas',
                        'data' => $payments_data,
                    ]);
                } else {
                    http_response_code(422);
                }
                break;
            
            default:
                http_response_code(405);
                header('Allow: POST');
        }
    }
    
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['sum'])) {
            $errors[] = "Laukas 'Suma' privalo būti užpildytas";
        } elseif (!is_numeric($data['sum'])) {
            $errors[] = "Laukas 'Suma' privalo būti skaičius";
        } elseif ($data['sum'] <= 0) {
            $errors[] = "Laukas 'Suma' turi būti didesnis už 0";
        }

        return $errors;
    }

    private function get_payments(int $sum): array
    {
        $payment_quantity = 4;
        $percent = 0;
        foreach ($this->rates as $limit => $limit_percent) {
            if ($sum > $limit) {
                $percent = $limit_percent;
            }
        }
        $interest_sum = $sum / 100 * $percent;
        $interest_payment = $interest_sum / $payment_quantity;
        $payments = [];
        for ($i = 1; $i <= $payment_quantity; $i++) {
            $payment = $i != $payment_quantity ? $interest_payment : $interest_payment + $sum;
            $months_after = 12 / $payment_quantity * $i;
            $payments[] = [
                'date' => date('Y-m-d', strtotime("+$months_after months")),
                'payment' => number_format((float)$payment, 2, ',', ''),
            ];
        }

        return [
            'initial_sum' => $sum,
            'percent' => $percent,
            'interest_sum' => $interest_sum,
            'payments' => $payments,
        ];
    }
}









