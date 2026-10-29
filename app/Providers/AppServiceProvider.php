<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Serve livewire.js relative to the app's base URL so it also works from a subfolder
        config(['livewire.asset_url' => url('livewire/livewire.js')]);

        // ->whereCampusIn('students.campus', ['VC']): the campus column (which can be a list such as "MC, VC",
        // see App\Support\CampusList) contains one of the codes exactly. No codes = no rows.
        Builder::macro('whereCampusIn', function (string $column, array $campuses) {
            $wrapped = $this->getGrammar()->wrap($column);

            return $this->where(function ($query) use ($wrapped, $campuses) {
                if (! $campuses) {
                    $query->whereRaw('1 = 0');
                }
                foreach ($campuses as $campus) {
                    $query->orWhereRaw("FIND_IN_SET(?, REPLACE({$wrapped}, ' ', '')) > 0", [$campus]);
                }
            });
        });
    }
}
