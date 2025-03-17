<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIntroMessageRequest;
use App\Http\Requests\UpdateIntroMessageRequest;
use App\Http\Resources\IntroMessageResource;
use App\Http\Services\IntroMessageService;
use App\Models\IntroMessage;

class IntroMessageController extends Controller
{
    public function __construct(private readonly IntroMessageService $introMessageService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/intro-messages",
     *     summary="Retrieve a list of intro messages",
     *     description="Get a paginated list of intro messages",
     *     tags={"IntroMessages"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved intro messages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Intro messages retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SplashScreenSchema")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     * )
     */

    public function index()
    {
        $limit = request('limit', 15);
        $introMessages = $this->introMessageService->getAll($limit);

        return $this->success(IntroMessageResource::collection($introMessages), 'Intro messages retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/intro-messages",
     *     summary="Create a new intro message",
     *     description="Creates a new intro message with an image upload",
     *     tags={"IntroMessages"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create a new intro message",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreSplashScreenRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created intro message",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Intro message created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     * )
     */

    public function create(StoreIntroMessageRequest $request)
    {
        $validatedData = $request->validated();
        $introMessage = $this->introMessageService->create($validatedData);

        return $this->success(new IntroMessageResource($introMessage), 'Intro message created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/intro-messages/{id}",
     *     summary="Retrieve a specific intro message",
     *     description="Fetch the details of a specific intro message by its ID.",
     *     tags={"IntroMessages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the intro message to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved intro message",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Intro message retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/SplashScreenSchema"
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Intro message not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function show(IntroMessage $introMessage)
    {
        return $this->success(new IntroMessageResource($introMessage), 'Intro message retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/intro-messages/update/{id}",
     *     summary="Update an existing intro message",
     *     description="Update the intro message with the provided data. Only the fields provided in the request will be updated.",
     *     tags={"IntroMessages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the intro message to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateSplashScreenRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Intro message updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="message", type="string", example="Intro message updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation errors or missing required data",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The intro message with the given ID does not exist",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */

    public function update(UpdateIntroMessageRequest $request, IntroMessage $introScreen)
    {
        $validatedData = $request->validated();
        $introScreen = $this->introMessageService->update($validatedData, $introScreen);

        return $this->success(new IntroMessageResource($introScreen), 'Intro message updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/intro-messages/{id}",
     *     summary="Delete an intro message",
     *     description="Deletes the intro message identified by the given ID.",
     *     tags={"IntroMessages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the intro message to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Intro message deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="message", type="string", example="Intro message deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The intro message with the given ID does not exist",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     * )
     */


    public function delete(IntroMessage $introMessage)
    {
        $this->introMessageService->delete($introMessage->id);

        return $this->success(new IntroMessageResource($introMessage), 'Intro message deleted successfully');
    }
}
