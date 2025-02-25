<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSplashScreenRequest;
use App\Http\Requests\UpdateSplashScreenRequest;
use App\Http\Resources\SplashScreenResource;
use App\Http\Services\SplashScreenService;
use App\Models\SplashScreen;

class SplashScreenController extends Controller
{
    public function __construct(private readonly SplashScreenService $splashScreenService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/splash_screens",
     *     summary="Retrieve a list of splash screens",
     *     description="Get a paginated list of splash screens",
     *     tags={"SplashScreens"},
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
     *         description="Successfully retrieved splash screens",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Splash screens retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SplashScreenSchema")),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $limit = request('limit', 15);
        $splashScreens = $this->splashScreenService->getAll($limit);

        return $this->success(SplashScreenResource::collection($splashScreens), 'Splash screens retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/splash_screens",
     *     summary="Create a new splash screen",
     *     description="Creates a new splash screen with an image upload",
     *     tags={"SplashScreens"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create a new splash screen",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreSplashScreenRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created splash screen",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Splash screen created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     )
     * )
     */
    public function create(StoreSplashScreenRequest $request)
    {
        $validatedData = $request->validated();
        $splashScreen = $this->splashScreenService->create($validatedData);

        return $this->success(new SplashScreenResource($splashScreen), 'Splash screen created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/splash_screens/{id}",
     *     summary="Retrieve a specific splash screen",
     *     description="Fetch the details of a specific splash screen by its ID.",
     *     tags={"SplashScreens"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the splash screen to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved splash screen",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Splash screen retrieved successfully"),
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
     *         description="Splash screen not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Splash screen not found")
     *         )
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */

    public function show(SplashScreen $splashScreen)
    {
        return $this->success(new SplashScreenResource($splashScreen), 'Splash screen retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/splash_screens/update/{id}",
     *     summary="Update an existing splash screen",
     *     description="Update the splash screen with the provided data. Only the fields provided in the request will be updated.",
     *     tags={"SplashScreens"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the splash screen to update",
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
     *         description="Splash screen updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="message", type="string", example="Splash screen updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation errors or missing required data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The splash screen with the given ID does not exist",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Splash screen not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateSplashScreenRequest $request, SplashScreen $splashScreen)
    {
        $validatedData = $request->validated();
        $splashScreen = $this->splashScreenService->update($validatedData, $splashScreen);

        return $this->success(new SplashScreenResource($splashScreen), 'Splash screen updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/splash_screens/{id}",
     *     summary="Delete a splash screen",
     *     description="Deletes the splash screen identified by the given ID.",
     *     tags={"SplashScreens"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the splash screen to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Splash screen deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/SplashScreenSchema"),
     *             @OA\Property(property="message", type="string", example="Splash screen deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The splash screen with the given ID does not exist",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Splash screen not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid data or request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request")
     *         )
     *     )
     * )
     */

    public function delete(SplashScreen $splashScreen)
    {
        $this->splashScreenService->delete($splashScreen->id);

        return $this->success(new SplashScreenResource($splashScreen), 'Splash screen deleted successfully');
    }
}
