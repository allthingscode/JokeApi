<?php


namespace App\Controller;

use App\Entity\Joke;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations AS OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class JokeController
 * @package App\Controller
 */
class JokeController extends AbstractController
{
    /**
     * @OA\Post(
     *
     *     path="/joke",
     *     description="Add A New Joke",
     *     operationId="Add Joke",
     *
     *     @OA\Parameter(
     *         name="joke",
     *         in="body",
     *         required=true,
     *         type="json",
     *         @OA\Schema(
     *             type="object",
     *             required={"joke"},
     *             @OA\Property(property="joke", type="string", example="Why did the chicken cross the road?"),
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="Joke Added Successfully"),
     *     @OA\Response(response="400", description="Request is Not Valid")
     *
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add( Request $request ): JsonResponse
    {
        $response = new JsonResponse();

        $rawPostData = json_decode($request->getContent(), true);
        $jokeString  = $rawPostData['joke'];

        // Validate the request
        if ( ! $jokeString ) {
            $response->setStatusCode(400);
            $response->setData('Missing joke text.');
            return $response;
        }

        // Insert the new joke
        $joke = new Joke();
        $joke->setJoke($jokeString);
        $this->getDoctrine()->getManager()->persist($joke);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(200);
        $response->setData($joke->toArray());

        return $response;
    }


    /**
     * @OA\Put(
     *
     *     path="/joke/{jokeId}",
     *     description="Update an Existing Joke",
     *     operationId="Update Joke",
     *
     *     @OA\Parameter(name="jokeId", in="path", required=true, type="integer"),
     *     @OA\Parameter(
     *         name="joke",
     *         in="body",
     *         required=true,
     *         type="json",
     *         @OA\Schema(
     *             type="object",
     *             required={"joke"},
     *             @OA\Property(property="joke", type="string", example="Why did the chicken cross the road?"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Joke Updated Successfully",
     *         @Model(type=Joke::class)
     *     ),
     *
     *     @OA\Response(response="400", description="Request is Not Valid"),
     *     @OA\Response(response="404", description="Joke Not Found")
     *
     * )
     *
     * @param Request $request
     * @param int $jokeId
     * @return JsonResponse
     */
    public function updateById( Request $request, int $jokeId ): JsonResponse
    {
        $response = new JsonResponse();

        $rawPutData = json_decode($request->getContent(), true);
        $jokeString  = $rawPutData['joke'];

        // Validate the request
        if ( ! $jokeString ) {
            $response->setStatusCode(400);
            $response->setData('Missing joke text.');
            return $response;
        }

        $joke = $this->getDoctrine()
            ->getRepository(Joke::class)
            ->find($jokeId);

        // If we didn't find anything, then the ID number is not in our database
        if ( ! $joke ) {
            $response->setStatusCode(404);
            return $response;
        }

        $joke->setJoke($jokeString);

        $this->getDoctrine()->getManager()->persist($joke);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(200);
        $response->setData($joke->toArray());

        return $response;
    }


    /**
     * @OA\Get(
     *
     *     path="/joke/{jokeId}",
     *     description="Get a Specific Joke",
     *     operationId="Get Joke",
     *
     *     @OA\Parameter(name="jokeId", required=true, in="path", type="integer"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Joke Retrieved Successfully",
     *         @Model(type=Joke::class)
     *     ),
     *
     *     @OA\Response(response="400", description="Request is Not Valid"),
     *     @OA\Response(response="404", description="Joke Not Found")
     *
     * )
     *
     * @param int $jokeId
     * @return JsonResponse
     */
    public function getById( int $jokeId ): JsonResponse
    {
        $response = new JsonResponse();

        $joke = $this->getDoctrine()
            ->getRepository(Joke::class)
            ->find($jokeId);

        // If we didn't find anything, then the ID number is not in our database
        if ( ! $joke ) {
            $response->setStatusCode(404);
            return $response;
        }

        $response->setStatusCode(200);
        $response->setData($joke->toArray());

        return $response;
    }


    /**
     * @OA\Get(
     *
     *     path="/joke/random",
     *     description="Get a Random Joke",
     *     operationId="Get Any Joke",
     *
     *     @OA\Response(
     *         response="200",
     *         description="Joke Retrieved Successfully",
     *         @Model(type=Joke::class)
     *     ),
     *
     *     @OA\Response(response="400", description="Request is Not Valid")
     *
     * )
     *
     * @param Request $request
     * @param int $jokeId
     * @return JsonResponse
     */
    public function getRandom(): JsonResponse
    {
        $response = new JsonResponse();

        $records = $this->getDoctrine()
            ->getRepository(Joke::class)
            ->findAll()
        ;

        // Yes, this is very inefficient.
        // It would be better to do this natively with the database.
        // However, when do we truly need a random function in real-life?
        if ( shuffle($records) === false ) {
            $response->setStatusCode(500);
            $response->setData('Unable to shuffle jokes.');
            return $response;
        }
        $joke = $records[0];

        $response->setStatusCode(200);
        $response->setData($joke->toArray());

        return $response;
    }


    /**
     *
     * @OA\Get(
     *
     *     path="/jokes",
     *     description="Get a Collection of Jokes",
     *     operationId="Get Jokes",
     *
     *     @OA\Parameter(name="page",         required=true, in="query", type="integer"),
     *     @OA\Parameter(name="itemsPerPage", required=true, in="query", type="integer"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Jokes Retrieved Successfully",
     *
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="meta",
     *                 @OA\Property(property="page",         type="integer", example=1),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=5),
     *                 @OA\Property(property="total",        type="integer", example=5)
     *             ),
     *             @OA\Property(
     *                 property="jokes",
     *                 type="array",
     *                 @OA\Items(ref=@Model(type=Joke::class))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response="400", description="Request is Not Valid")
     * )
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCollection( Request $request )
    {
        $response = new JsonResponse();

        $jokeCollection = [];

        $validationErrors = [];
        foreach( ['page', 'itemsPerPage'] as $param ) {
            if ( ! is_numeric($request->query->get($param)) ) {
                $validationErrors[] = "Missing or invalid '{$param}' query parameter.";
            }
            if ( $request->query->get($param) < 1 ) {
                $validationErrors[] = "'{$param}' must be greater than 0.";
            }
        }
        if ( ! empty($validationErrors) ) {
            $response->setStatusCode(400);
            $response->setData(implode("\n", $validationErrors));
            return $response;
        }

        $offset = ($request->query->get('page') - 1) * $request->query->get('itemsPerPage');
        $limit  = $request->query->get('itemsPerPage');

        // Retrieve jokes
        $records = $this->getDoctrine()
            ->getRepository(Joke::class)
            ->findBy(
                [],
                ['id' => 'ASC'],
                $limit,
                $offset
            )
        ;

        // Assemble our response
        foreach( $records as $record ) {
            $jokeCollection[] = $record->toArray();
        }
        $responseData = [
            'meta' => [
                'page'         => $request->query->get('page'),
                'itemsPerPage' => $request->query->get('itemsPerPage'),
                'total'        => count($jokeCollection)
            ],
            'jokes' => $jokeCollection
        ];

        $response->setStatusCode(200);
        $response->setData($responseData);

        return $response;
    }


    /**
     * @OA\Delete(
     *
     *     path="/joke/{jokeId}",
     *     description="Delete a Specific Joke",
     *     operationId="Delete Joke",
     *
     *     @OA\Parameter(name="jokeId", in="path", required=true, type="integer"),
     *
     *     @OA\Response(response="200", description="Joke Deleted Successfully"),
     *     @OA\Response(response="400", description="Request is Not Valid"),
     *     @OA\Response(response="404", description="Joke Not Found")
     *
     * )
     *
     * @param Request $request
     * @param int $jokeId
     * @return JsonResponse
     */
    public function deleteById( int $jokeId ): JsonResponse
    {
        $response = new JsonResponse();

        $joke = $this->getDoctrine()
            ->getRepository(Joke::class)
            ->find($jokeId);

        // If we didn't find anything, then the ID number is not in our database
        if ( ! $joke ) {
            $response->setStatusCode(404);
            return $response;
        }

        $this->getDoctrine()->getManager()->remove($joke);
        $this->getDoctrine()->getManager()->flush();

        $response->setStatusCode(200);

        return $response;
    }
}