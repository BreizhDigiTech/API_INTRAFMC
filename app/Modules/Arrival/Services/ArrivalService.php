<?php

namespace App\Modules\Arrival\Services;

use App\Models\CbdArrival;
use App\Models\ArrivalProductCbd;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;

class ArrivalService
{
    /**
     * Valide un arrivage.
     *
     * @param int $arrivalId
     * @return CbdArrival
     * @throws CustomException
     */
    public function validateArrival($arrivalId)
    {
        $arrival = CbdArrival::findOrFail($arrivalId);

        if ($arrival->status === 'validated') {
            throw new CustomException('Erreur de validation', "L'arrivage est déjà validé.");
        }

        $arrival->update(['status' => 'validated']);

        return $arrival->load('products');
    }

    /**
     * Crée un nouvel arrivage.
     *
     * @param array $input
     * @return CbdArrival
     * @throws CustomException
     */
    public function createArrival($input)
    {
        $this->validateArrivalInput($input);

        return DB::transaction(function () use ($input) {
            $arrival = CbdArrival::create([
                'amount' => $input['amount'],
                'status' => $input['status'],
            ]);

            foreach ($input['products'] as $productInput) {
                ArrivalProductCbd::create([
                    'arrival_id' => $arrival->id,
                    'product_id' => $productInput['product_id'],
                    'quantity' => $productInput['quantity'],
                    'unit_price' => $productInput['unit_price'],
                ]);
            }

            return $arrival;
        });
    }

    /**
     * Met à jour un arrivage existant.
     *
     * @param int $arrivalId
     * @param array $input
     * @return CbdArrival
     * @throws CustomException
     */
    public function updateArrival($arrivalId, $input)
    {
        $arrival = CbdArrival::findOrFail($arrivalId);

        $updateData = [];
        if (isset($input['amount'])) {
            $updateData['amount'] = $input['amount'];
        }
        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }

        if (!empty($updateData)) {
            $arrival->update($updateData);
        }

        if (isset($input['products'])) {
            $arrivalProducts = ArrivalProductCbd::where('arrival_id', $arrival->id)->get();

            foreach ($input['products'] as $productInput) {
                $arrivalProduct = $arrivalProducts->firstWhere('product_id', $productInput['product_id']);
                if ($arrivalProduct) {
                    $arrivalProduct->update([
                        'quantity' => $productInput['quantity'] ?? $arrivalProduct->quantity,
                        'unit_price' => $productInput['unit_price'] ?? $arrivalProduct->unit_price,
                    ]);
                }
            }
        }

        return $arrival->load('products');
    }

    /**
     * Supprime un arrivage.
     *
     * @param int $arrivalId
     * @return CbdArrival
     * @throws CustomException
     */
    public function deleteArrival($arrivalId)
    {
        $arrival = CbdArrival::findOrFail($arrivalId);

        if ($arrival->status === 'validated') {
            throw new CustomException("Impossible de supprimer un arrivage validé.");
        }

        $arrival->products()->delete();
        $arrival->delete();

        return $arrival;
    }

    /**
     * Valide les données d'entrée pour un arrivage.
     *
     * @param array $input
     * @throws CustomException
     */
    private function validateArrivalInput(array $input)
    {
        $validator = validator($input, [
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,validated',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:cbd_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new CustomException('Erreur de validation', implode(' ', $validator->errors()->all()));
        }
    }
}