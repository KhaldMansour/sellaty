<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get a list of all pages.
     *
     * @OA\Get(
     *    security={
     *         {"bearerAuth": {}}
     *      },
     *     path="/api/v1/pages",
     *     operationId="getPages",
     *     tags={"Pages"},
     *     summary="Get a list of all pages",
     *     description="Returns a list of all pages",
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="The number of items to return",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/PageSchema")
     *          )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        $pages = Page::paginate($limit);

        return $this->success(PageResource::collection($pages), 'Pages retrieved successfully');
    }

    /**
     * Store a newly created page in storage.
     *
     * @OA\Post(
     *      path="/api/v1/pages/",
     *      operationId="storePage",
     *      tags={"Pages"},
     *      summary="Store a newly created page",
     *      description="Store a newly created page",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StorePageRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Page created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/PageSchema")
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
     *      security={
     *         {"bearerAuth": {}}
     *      }
     * )
     */
    public function store(StorePageRequest $request)
    {
        $data = $request->validated();
        $page = Page::create($data);

        return $this->success(new PageResource($page), 'Page created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pages/{page}",
     *     summary="Get a single page by its slug",
     *     description="Retrieve a single page by its slug",
     *     operationId="getPageBySlug",
     *     tags={"Pages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="Slug of the page",
     *         in="path",
     *         name="page",
     *         required=true,
     *         example="about-us",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page retrieved successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 ref="#/components/schemas/PageSchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *     )
     * )
     */
    public function show(Page $page)
    {
        if (!$page->published) {
            abort(404);
        }

        $locale = app()->getLocale();

        $title = $page->getTranslation('title', $locale);

        $content = $page->getTranslation('content', $locale);

        $data = [
            'title' => $title,
            'content' => $content
        ];

        return view('pages.show', compact('data'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:pages,slug,' . $page->id,
            'content' => 'sometimes|required|string',
            'published' => 'boolean',
        ]);

        $page->update($data);

        return $this->success(new PageResource($page), 'Page updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/pages/{page}",
     *     summary="Delete a page",
     *     description="Deletes the page identified by the given ID.",
     *     tags={"Pages"},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="ID of the page to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Page deleted successfully")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return $this->success(null, 'Page deleted successfully');
    }
}
