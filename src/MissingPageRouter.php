<?php

namespace Spatie\MissingPageRedirector;

use Exception;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class MissingPageRouter
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var array */
    protected $redirects;

    public function __construct (Router $router)
    {
        $this->router = $router;
    }

    public function setRedirects(array $redirects)
    {
        $this->redirects  = $redirects;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return RedirectResponse|null
     */
    public function getRedirectFor(Request $request)
    {

        collect($this->redirects)->each(function($redirectUrl, $missingUrl) {
            $this->router->get($missingUrl, function () use ($redirectUrl) {
                $redirectUrl = $this->resolveRouterParameters($redirectUrl);

                return redirect()->to($redirectUrl);
            });
        });

        try {
            return $this->router->dispatch($request);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function resolveRouterParameters(string $redirectUrl): string
    {
        $routeParameters = $this->router->getCurrentRoute()->parameters();

        foreach($routeParameters as $key=>$value) {
            $redirectUrl = str_replace('{' . $key . '}', $value, $redirectUrl);
        }
        return $redirectUrl;
    }

}