<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Product;
use App\Services\OfferService;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $offerService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offers/products/{product}",
     *     summary="Create an offer on a product",
     *     description="Creates a new offer for a specific product and initiates a chat between the user and the seller.",
     *     operationId="createOffer",
     *     tags={"Offers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID of the product the offer is being made on",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateOfferRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Offer and associated chat created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Offer created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="offer", ref="#/components/schemas/OfferSchema"),
     *                 @OA\Property(property="chat", ref="#/components/schemas/ChatSchema")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="text",
     *                     type="array",
     *                     @OA\Items(type="string", example="The text field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="array",
     *                     @OA\Items(type="string", example="The price must be at least 0.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function create(CreateOfferRequest $request , Product $product)
    {
        $user = auth()->user();
        $offerWithChat = $this->offerService->createOffer($request->validated(), $user , $product);

        return $this->success(['offer' => new OfferResource($offerWithChat['offer']) , 'chat' => $offerWithChat['chat']]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        //
    }
}
