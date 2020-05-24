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

class SandController extends AbstractController
{
    /**
     * @Route("/info")
     */
    public function infoAction(Request $request)
    {
        phpinfo();
    }

    /**
     * @Route("/test")
     */
    public function testAction(Request $request)
    {
        $m = $this->getDoctrine()->getManager();
        $noteId = 10;
        $hotelId = 52;
        $note = $m->find(Note::class, $noteId) ?? Note::create($noteId, 'Note 10');
        $hotel = $m->find(Hotel::class, $hotelId) ?? Hotel::create($hotelId, 'Hotel 52');
        $m->persist($note);
        $m->persist($hotel);


        $m->flush();

        return new JsonResponse([
            'date-time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'url-demo' => $this->generateUrl('demo1', ['id' => 10]),
            'url-demo-pc' => $this->generateUrl('demo_pc', ['note' => $note->getId()]),
            'url-hotel-en' => $this->generateUrl('hotel_show.en', ['hotel' => $hotel->getId()]),
            'url-hotel-fr' => $this->generateUrl('hotel_show.fr', ['hotel' => $hotel->getId()]),
        ]);
    }

    /**
     * @Route("sand/demo1/{id}", name="demo1")
     * @Hash("id")
     * @param Request $request
     * @param         $id
     */
    public function demo1Action(Request $request, $id)
    {
        throw new \InvalidArgumentException($id);
        return new Response($id);
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
        return new Response($note->getId());
    }

    /**
     * @route({
     * "en": "/show-{hotel}",
     * "fr": "/explorer-{hotel}"
     * }, name="hotel_show", methods={"GET"})
     *
     * @Hash("hotel")
     * @ParamConverter("hotel", class="Hotel")
     *
     */
    public function show(Request $request, Hotel $hotel)
    {
        return new Response(print_r($hotel, true));
    }
}
