<x-app-layout>
    <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
        <div class="relative flex-1 w-full px-3 sm:px-4 mt-6 sm:mt-10 pb-10">
            <div
                class="fixed inset-0 pointer-events-none bg-no-repeat z-0"
                style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 40px); opacity: 0.3"
            ></div>

            <div class="max-w-5xl mx-auto relative z-10">
                <div class="shadow rounded p-6 sm:p-8" style="background-color: rgb(215, 183, 142)">
                    <h1 class="text-2xl sm:text-3xl font-bold text-black mb-6">
                        Apie mus
                    </h1>

                    <div class="space-y-5 text-black leading-relaxed text-sm sm:text-base">
                        <p>
                            Ši platforma sukurta kaip skaitmenizuota erdvė prekybai ir paslaugų teikimui, kurioje vienodai svarbūs tiek prekės, tiek paslaugos. Skirtingai nuo įprastų el. parduotuvių, kuriose dažniausiai dominuoja fiziniai gaminiai, ši sistema nuo pat pradžių buvo kuriama taip, kad paslaugų teikėjai turėtų tokias pačias galimybes būti matomi, randami ir pasirenkami kaip ir prekių pardavėjai.
                        </p>

                        <p>
                            Platformos tikslas: padėti kūrėjams, amatininkams, tautodailininkams ir smulkiesiems paslaugų teikėjams lengviau persikelti į skaitmeninę erdvę. Daugeliui jų didžiausias iššūkis yra matomumas internete, paprastas savo veiklos pristatymas ir patogus klientų užsakymų valdymas. Todėl ši svetainė veikia ne tik kaip el. prekyvietė, bet ir kaip praktinis įrankis, leidžiantis vienoje vietoje pateikti savo darbus, siūlomas paslaugas ir pasiekti platesnę auditoriją.
                        </p>

                        <p>
                            Prekių pardavėjams platforma suteikia įprastą el. prekybos funkcionalumą: skelbimų kūrimą, prekių rodymą, pirkimą svetainės viduje ir patogų užsakymų stebėjimą. Tuo pačiu paslaugų teikėjams sukurta atskira logika, leidžianti ne tik pristatyti savo paslaugas, bet ir valdyti užsakymus per tam skirtą vidinę sistemą. Viena svarbiausių dalių yra užsakymų valdymo lenta, kuri padeda aiškiai matyti paslaugų eigą, susitarimus, pirkėjų informaciją ir vykdomus darbus vienoje vietoje.
                        </p>

                        <p>
                            Tokiu būdu platforma tampa naudinga ne tik pirkėjui, kuris gali patogiai rasti tiek gaminius, tiek paslaugas, bet ir pačiam pardavėjui ar kūrėjui, kuriam svarbu turėti paprastą, aiškią ir praktišką darbo aplinką. Sistema padeda mažinti skaitmenizacijos barjerą tiems, kurie nori pristatyti savo veiklą internete, tačiau neturi atskiros svetainės ar sudėtingų administravimo įrankių.
                        </p>

                        <p>
                            Ypatingas dėmesys skiriamas tautodailininkams, kūrėjams ir smulkiesiems vietos veiklos vykdytojams, kuriems dažnai reikia didesnio matomumo ir modernesnių būdų pasiekti klientą. Ši platforma siekia padėti jų veiklai tapti labiau pastebimai, lengviau prieinamai ir geriau pritaikytai šiuolaikiniam pirkėjo įpročiui ieškoti visko internetu.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            @include('components.footer')
        </div>
    </div>
</x-app-layout>
