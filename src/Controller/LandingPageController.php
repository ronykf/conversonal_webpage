<?php

namespace App\Controller;

use App\Entity\BetaSignup;
use App\Repository\BetaSignupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController
{
    #[Route('/', name: 'landing_page', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        BetaSignupRepository $betaSignupRepository
    ): Response {
        $success = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');
            $workEmail = $request->request->get('work_email');
            $phoneNumber = $request->request->get('phone_number');

            // Validate inputs
            if (empty($firstName) || empty($lastName) || empty($workEmail) || empty($phoneNumber)) {
                $error = 'All fields are required.';
            } elseif (!filter_var($workEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please provide a valid email address.';
            } elseif ($betaSignupRepository->emailExists($workEmail)) {
                $error = 'This email is already registered for beta access.';
            } else {
                // Create and save the beta signup
                $betaSignup = new BetaSignup();
                $betaSignup->setFirstName(htmlspecialchars(trim($firstName)));
                $betaSignup->setLastName(htmlspecialchars(trim($lastName)));
                $betaSignup->setWorkEmail(strtolower(trim($workEmail)));
                $betaSignup->setPhoneNumber(htmlspecialchars(trim($phoneNumber)));

                $entityManager->persist($betaSignup);
                $entityManager->flush();

                $success = true;
            }
        }

        return $this->render('landing_page/index.html.twig', [
            'success' => $success,
            'error' => $error,
        ]);
    }
}
