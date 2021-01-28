<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use App\Utils\Lang;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeType;
use July\Node\NodeField;
use July\Taxonomy\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Config\PartialView;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nodes = Node::index();

        return view('backend::node.index', [
                'nodes' => $nodes,
                'node_types' => NodeType::all()->pluck('label', 'id')->all(),
                'catalogs' => Catalog::all()->pluck('label', 'id')->all(),
                // 'catalogs' => ['main' => '默认目录'],
                // 'tags' => Tag::allTags(),
                'languages' => Lang::getTranslatableLangnames(),
            ]);
    }

    /**
     * 选择类型
     *
     * @return \Illuminate\Http\Response
     */
    public function chooseNodeType()
    {
        return view_with_langcode('backend::node.choose_node_type', [
                'node_types' => NodeType::all()->all(),
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \July\Core\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function create(NodeType $nodeType)
    {
        // $nodes = Node::all()->map(function(Node $node) {
        //     return [
        //         'id' => $node->id,
        //         'title' => $node->title,
        //     ];
        // })->keyBy('id')->all();

        $langcode = langcode('content');
        $node = new Node([
                'node_type_id' => $nodeType->getKey(),
                'langcode' => $langcode,
            ]);

        $data = [
            'node' => array_merge($node->gather(), ['path' => '']),
            'node_type' => [
                'id' => $nodeType->getKey(),
                'label' => $nodeType->label,
            ],
            'fields' => $node->retrieveFormMaterials(),
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                // 'nodes' => $nodes,
                'templates' => $this->getTwigTemplates($langcode),
                // 'catalog_nodes' => Catalog::allPositions(),
                // 'editor_config' => Config::getEditorConfig(),
            ],
            'langcode' => $langcode,
            'mode' => 'create',
        ];

        return view('backend::node.create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $node = Node::create($request->all());
        return response([
            'node_id' => $node->getKey(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function show(Node $content)
    {
        //
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \July\Core\Node\Node  $node
     * @param  string|null  $translateTo
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, string $translateTo = null)
    {
        if ($translateTo) {
            config(['request.langcode.content' => $translateTo]);
            $node->translateTo($translateTo);
        }
        $langcode = $node->getLangcode();

        $data = [
            'node' => array_merge($node->gather(), ['path' => $node->getEntityPath()]),
            'node_type' => [
                'id' => $node->nodeType->id,
                'label' => $node->nodeType->label,
            ],
            'fields' => $node->retrieveFieldMaterials(),
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                // 'nodes' => $this->simpleNodes($langcode),
                'templates' => $this->getTwigTemplates($langcode),
                // 'catalog_nodes' => Catalog::allPositions(),
                // 'editor_config' => Config::getEditorConfig(),
            ],
            'langcode' => $langcode,
            'mode' => ($translateTo && $translateTo !== $langcode) ? 'translate' : 'edit',
        ];

        // dd($data);

        return view_with_langcode('backend::node.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Core\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Node $node)
    {
        $changed = (array) $request->input('_changed');

        if (!empty($changed)) {
            // Log::info($changed);
            $node->update($request->only($changed));
        }

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function destroy(Node $node)
    {
        // Log::info($node->id);
        $node->delete();

        return response('');
    }

    /**
     * 选择语言
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function chooseLanguage(Node $node)
    {
        if (!config('language.multiple')) {
            abort(404);
        }

        return view_with_langcode('backend::languages', [
            'original_langcode' => $node->getAttribute('langcode'),
            'languages' => Lang::getTranslatableLangnames(),
            'entityKey' => $node->getKey(),
            'routePrefix' => 'nodes',
        ]);
    }

    protected function simpleNodes(string $langcode = null)
    {
        return Node::all()->map(function (Node $node) use ($langcode) {
            if ($langcode) {
                $node->translateTo($langcode);
            }
            return [
                'id' => $node->getKey(),
                'title' => $node->getAttributeValue('title'),
            ];
        })->keyBy('id')->all();
    }

    /**
     * 渲染内容
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        // $contents = Node::carryAll();
        if ($ids = $request->input('selected_nodes')) {
            $nodes = Node::find($ids);
        } else {
            $nodes = Node::all();
        }

        // 多语言生成
        if (config('language.multiple')) {
            $langs = $request->input('langcode') ?: lang()->getAccessibleLangcodes();
        } else {
            $langs = [langcode('frontend')];
        }

        $success = [];
        foreach ($nodes as $node) {
            $result = [];
            foreach ($langs as $langcode) {
                try {
                    $node->translateTo($langcode)->render();
                    $result[$langcode] = true;
                } catch (\Throwable $th) {
                    $result[$langcode] = false;
                }
            }
            $success[$node->id] = $result;
        }

        return response($success);
    }

    /**
     * @return array
     */
    protected function getTwigTemplates(string $langcode)
    {
        $templates = PartialView::query()->where('langcode', $langcode)->get()->pluck('view');

        return array_values($templates->sort()->unique()->all());
    }
}
