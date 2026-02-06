<?php

namespace App\Controller;

use App\Repository\BetaSignupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BetaSignupListController extends AbstractController
{
    private const ACCESS_CODE = 'C0nv3r$0na1_beta_signup_list';

    #[Route('/beta-signup', name: 'beta_signup_access', methods: ['GET', 'POST'])]
    public function access(Request $request, SessionInterface $session): Response
    {
        // Check if already authenticated
        if ($session->get('beta_list_authenticated') === true) {
            return $this->redirectToRoute('beta_signup_list');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $accessCode = $request->request->get('access_code');

            if ($accessCode === self::ACCESS_CODE) {
                $session->set('beta_list_authenticated', true);
                return $this->redirectToRoute('beta_signup_list');
            } else {
                $error = 'Invalid access code. Please try again.';
            }
        }

        return $this->render('beta_signup/access.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/beta-signup/list', name: 'beta_signup_list', methods: ['GET'])]
    public function list(SessionInterface $session, BetaSignupRepository $betaSignupRepository): Response
    {
        // Check authentication
        if ($session->get('beta_list_authenticated') !== true) {
            return $this->redirectToRoute('beta_signup_access');
        }

        $signups = $betaSignupRepository->findAll();

        return $this->render('beta_signup/list.html.twig', [
            'signups' => $signups,
        ]);
    }

    #[Route('/beta-signup/logout', name: 'beta_signup_logout', methods: ['GET'])]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('beta_list_authenticated');
        return $this->redirectToRoute('beta_signup_access');
    }
}
