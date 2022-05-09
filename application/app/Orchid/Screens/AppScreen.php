<?php

namespace App\Orchid\Screens;

use App\Models\Api\App;
use App\Models\Api\Link;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;
use Orchid\Platform\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Range;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\UTM;
use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Ramsey\Uuid\Uuid;

class AppScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'apps' => App::orderBy('created_at', 'DESC')->paginate(30),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Приложения';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Страница создания и просмотра приложений';
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @throws \Throwable
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::columns([

                Layout::rows([

                    Input::make('name')
                        ->title('Название приложения')
                        ->required(true)
                        ->popover('Введите название для приложения'),

                    Input::make('url')
                        ->title('Ссылка на приложение')
                        ->required(true)
                        ->popover('Введите ссылку для приложения'),

                    Button::make('Сохранить')->method('save')
                        ->type(Color::DARK()),

                    ]),
                ]),

            Layout::table('apps', [
                TD::make('name')->width(150),
                TD::make('created_at')->render(function ($link) {
                    return Carbon::parse($link->created_at)->format('Y-m-d H:i:s');
                }),//->width(200)->defaultHidden(true),

                TD::make('uuid')->align(TD::ALIGN_CENTER),//->width(170),
                TD::make('url')->align(TD::ALIGN_CENTER),//->width(400),
                TD::make('is_work')->align(TD::ALIGN_CENTER),
            ]),
        ];
    }

    public function save(Request $request)
    {
        try {
            $app = App::query()->create([
                'name' => $request->name,
                'uuid' => Uuid::uuid4()->toString(),
                'url'  => $request->url,
            ]);

            Alert::success('Приложение успешно создано, его uuid : '.$app->uuid);

        } catch (\Throwable $exception) {

            Toast::error('При создании возникла ошибка');
        }
    }
}
