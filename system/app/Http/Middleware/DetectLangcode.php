<?php

namespace App\Http\Middleware;

use Closure;

class DetectLangcode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $config = config();

        // 设置内容语言
        if ($clang = $request->input('content_langcode')) {
            $config->set('request.langcode.content', $clang);
        }

        // 设置页面语言
        $config->set('request.langcode.current_page', $this->getPageLangcode($request));

        return $next($request);
    }

    protected function getPageLangcode($request)
    {
        $uri = trim(str_replace('\\', '/', $request->getRequestUri()), '/');
        if (strpos($uri, config('jc.admin_prefix', 'admin').'/') === 0) {
            return config('jc.langcode.admin_page');
        }

        $dirs = explode('/', $uri);
        if (langname($langcode = $dirs[0] ?? null)) {
            return $langcode;
        }

        return config('jc.langcode.page');
    }
}
