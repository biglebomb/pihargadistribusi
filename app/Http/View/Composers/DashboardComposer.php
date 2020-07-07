<?php


namespace App\Http\View\Composers;


use App\Models\Lini;
use Illuminate\View\View;

class DashboardComposer
{
    protected $linis;
    public function __construct()
    {
        $this->linis = Lini::all();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('linis', $this->linis);
    }
}
