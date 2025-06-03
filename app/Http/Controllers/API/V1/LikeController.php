<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleLikeRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Product;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LikeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/likes/toggle-like",
     *     summary="Toggle like or unlike a product or user",
     *     description="Allows an authenticated user to like or unlike either a product or a user. You must provide either `product_id` or `user_id`, but not both.",
     *     operationId="toggleLike",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ToggleLikeRequestSchema")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like or Unlike action completed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liked successfully"),
     *             @OA\Property(property="data", type="string", example="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - both product_id and user_id provided, or none provided",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function toggle(ToggleLikeRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        $likeable = isset($validated['product_id']) ? Product::findOrFail($validated['product_id']) : User::findOrFail($validated['user_id']);

        $alreadyLiked = $user->hasLiked($likeable);

        $alreadyLiked
            ? $user->unlike($likeable)
            : $user->like($likeable);

        $message = $alreadyLiked ? 'Unliked successfully' : 'Liked successfully';

        return $this->success('', $message);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/likes/liked-users",
     *     summary="Get users liked by the authenticated user",
     *     description="Retrieves a list of users that the authenticated user has liked.",
     *     operationId="getLikedUsers",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of liked users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liked users fetched successfully"),
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
    public function getLikedUsers()
    {
        $user = auth()->user();

        $likedUsers = $user->likedUsers()->paginate();

        return $this->success(UserResource::collection($likedUsers), 'Liked users fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/likes/liked-products",
     *     summary="Get products liked by the authenticated user",
     *     description="Retrieves a list of products that the authenticated user has liked.",
     *     operationId="getLikedProducts",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of liked products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liked products fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductSchema")
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
    public function getLikedProducts()
    {
        $user = auth()->user();

        $likedUsers = $user->likedProducts()->paginate();

        return $this->success(ProductResource::collection($likedUsers), 'Liked products fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}/followers",
     *     summary="Get followers of a specific user",
     *     description="Returns a paginated list of users who follow the given user. Will return 403 if the user is locked.",
     *     operationId="getUserFollowers",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user whose followers are being fetched",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Limit number for pagination",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followers list fetched successfully",
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
     *         response=403,
     *         description="User is locked",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User is locked")
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
    public function getUserFollowers(User $user)
    {
        $limit = request('limit') ?? 10;

        if ($user->locked) {
            throw new HttpException(403, 'User is locked');
        }

        return $this->success(UserResource::collection($user->followers()->paginate($limit)), 'Followers fetched successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user_id}/followings",
     *     summary="Get users followed by the specified user",
     *     description="Retrieves a paginated list of users that the specified user is following. Returns 403 if the user is locked.",
     *     operationId="getUserFollowing",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="ID of the user whose followings are being fetched",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Limit number for pagination",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of followings",
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
     *         response=403,
     *         description="User is locked",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User is locked")
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
    public function getUserFollowing(User $user)
    {
        $limit = request('limit') ?? 10;

        if ($user->locked) {
            throw new HttpException(403, 'User is locked');
        }

        return $this->success(UserResource::collection($user->followings()->paginate($limit)), 'Followers fetched successfully');
    }
}
