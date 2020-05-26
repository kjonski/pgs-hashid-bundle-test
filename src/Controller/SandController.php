<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pgs\HashIdBundle\Annotation\Hash;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SandController extends AbstractController
{
    /**
     * @Route("/examples-list")
     */
    public function examplesListAction(Request $request)
    {
        $m = $this->getDoctrine()->getManager();
        $noteId = 10;
        $hotelId = 8;
        $note = $m->find(Note::class, $noteId) ?? Note::create($noteId, 'Note 10');
        $hotel = $m->find(Hotel::class, $hotelId) ?? Hotel::create($hotelId, 'Hotel 52');
        $m->persist($note);
        $m->persist($hotel);

        $m->flush();

        return new JsonResponse([
            'url-demo' => $this->createCase('demo1', ['id' => 10]),
            'url-demo-2-parameters-1-encoded' => $this->createCase('demo-2-parameters-1', ['id' => 10, 'id2' => 5]),
            'url-demo-2-parameters-2-encoded' => $this->createCase('demo-2-parameters-2', ['id' => 10, 'id2' => 5]),
            'url-demo-with-param-converter' => $this->createCase('demo_pc', ['note' => $note->getId()]),
            'url-hotel-en-direct' => $this->createCase('hotel_show.en', ['hotel' => $hotel->getId()]),
            'url-hotel-fr-direct' => $this->createCase('hotel_show.fr', ['hotel' => $hotel->getId()]),
            'url-hotel-en-locale' => $this->createCase('hotel_show', ['hotel' => $hotel->getId(), '_locale' => 'en']),
            'url-hotel-fr-locale' => $this->createCase('hotel_show', ['hotel' => $hotel->getId(), '_locale' => 'fr']),
            'url-hotel-without-locale' => $this->createCase('hotel_show', ['hotel' => $hotel->getId()]),
        ]);
    }

    private function createCase($route, $params)
    {
        return [
            'route_name' => $route,
            'original_parameters' => $params,
            'url' => $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    /**
     * @Route("sand/demo1/{id}", name="demo1")
     * @Hash("id")
     * @param Request $request
     * @param         $id
     */
    public function demo1Action(Request $request, $id)
    {
        return new Response($id);
    }

    /**
     * @Route("sand/demo-2-parameters-1/{id}/{id2}", name="demo-2-parameters-1")
     * @Hash("id")
     * @param Request $request
     * @param         $id
     * @param         $id2
     *
     * @return Response
     */
    public function demo2Parameters1Action(Request $request, $id, $id2)
    {
        return new Response(sprintf('%d, %d', $id, $id2));
    }

    /**
     * @Route("sand/demo-2-parameters-2/{id}/{id2}", name="demo-2-parameters-2")
     * @Hash({"id", "id2"})
     * @param Request $request
     * @param         $id
     * @param         $id2
     *
     * @return Response
     */
    public function demo2Parameters2Action(Request $request, $id, $id2)
    {
        return new Response(sprintf('%d, %d', $id, $id2));
    }

    /**
     * @Route("sand/demo_pc/{note}", name="demo_pc")
     * @Hash("note")
     * @ParamConverter("note", class="Note")
     * @param Request $request
     * @param Note    $note
     *
     * @return Response
     */
    public function demoParamConverterAction(Request $request, Note $note)
    {
        return new JsonResponse([
            'object_class' => get_class($note),
            'object_id' => $note->getId()
        ]);
    }

    /**
     * @route({
     * "en": "/show-{hotel}",
     * "fr": "/explorer-{hotel}"
     * }, name="hotel_show", methods={"GET"})
     *
     * @hash("hotel")
     * @ParamConverter("hotel", class="App\Entity\Hotel")
     *
     */
    public function show(EntityManagerInterface $em, Hotel $hotel)
    {
        return new Response(print_r($hotel, true));
    }
}
