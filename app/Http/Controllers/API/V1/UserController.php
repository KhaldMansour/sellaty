<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RecentSearchResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WantedProductResource;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Repositories\RecentSearchRepository;
use App\Repositories\WantedProductRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly RecentSearchRepository $recentSearchRepository, private readonly ProductRepository $productRepository, private readonly WantedProductRepository $wantedProductRepository)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/wanted-products",
     *     summary="Get a list of the authenticated user's wanted products",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=10,
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of the user's wanted products",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/WantedProductSchema")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No valid token provided",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Unauthorized"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function myWantedProducts(Request $request)
    {
        $limit = $request->input('limit', 10);

        $wantedProducts = $this->wantedProductRepository->where('user_id', auth()->user()->id)->paginate($limit);

        return $this->success(WantedProductResource::collection($wantedProducts));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/products",
     *     summary="Get a list of the authenticated user's products",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=10,
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of the user's products",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProductSchema")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No valid token provided",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Unauthorized"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function myProducts(Request $request)
    {
        $limit = $request->input('limit', 10);

        $products = $this->productRepository->where('user_id', auth()->user()->id)->paginate($limit);

        return $this->success(ProductResource::collection($products));
    }

    public function myRecentSearches()
    {
        $user = auth()->user();

        $recentSearches = $this->recentSearchRepository->where('user_id', $user->id)->limit(10)->get();

        return $this->success(RecentSearchResource::collection($recentSearches));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/profile/update",
     *     summary="Update authenticated user's profile",
     *     description="Allows the currently authenticated user to update their profile details.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateUserRequestSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function updateProfile(UpdateUserRequest $request, User $user)
    {
        $user = auth()->user();

        $user->update($request->validated());

        $user->loadCount(['followers', 'followings']);

        return $this->success(new UserResource($user), 'Profile updated successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile",
     *     summary="Get authenticated user profile",
     *     description="Returns the currently authenticated user's profile information.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function profile()
    {
        $user = auth()->user();

        $user->loadCount(['followers', 'followings']);

        return $this->success(new UserResource($user), 'Profile fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}",
     *     summary="Get user profile data",
     *     description="Retrieve the profile data of a specific user by their ID, excluding sensitive fields like password, created_at, and updated_at.",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User data fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function getUserData($userId)
    {
        $user = User::withCount(['followers', 'followings'])->findOrFail($userId);

        $user->makeHidden(['password' , 'created_at' , 'updated_at']);

        return $this->success(new UserResource($user), 'User data fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/my-followers",
     *     summary="Get followers of the authenticated user",
     *     description="Retrieves a paginated list of users who follow the authenticated user.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followers fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Followers fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserSchema")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function myFollowers()
    {
        $limit = request()->input('limit', 10);

        $user = auth()->user();

        return $this->success(UserResource::collection($user->followers()->paginate($limit)), 'Followers fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/my-followings",
     *     summary="Get users followed by the authenticated user",
     *     description="Retrieves a paginated list of users the authenticated user is following.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followings fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Followings fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserSchema")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function myFollowings()
    {
        $limit = request()->input('limit', 10);

        $user = auth()->user();

        return $this->success(UserResource::collection($user->followings()->paginate($limit)), 'Followings fetched successfully');
    }
}
