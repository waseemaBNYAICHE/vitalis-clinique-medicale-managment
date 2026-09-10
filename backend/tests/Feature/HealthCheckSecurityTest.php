<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * SCRUM-526 - Exposition de la route publique /api/health.
 *
 * Cette route doit rester accessible sans jeton : les sondes Docker, le
 * workflow de deploiement et scripts/docker-smoke-test.sh l'interrogent sans
 * pouvoir s'authentifier. Elle est donc lisible par n'importe qui sur
 * internet, et ne doit rien reveler de l'infrastructure.
 *
 * Ces tests n'utilisent volontairement pas RefreshDatabase : le scenario
 * "base indisponible" coupe la connexion, ce qui empecherait le nettoyage de
 * fin de test. La route ne lit aucune table, la migration est donc inutile.
 */
class HealthCheckSecurityTest extends TestCase
{
    public function test_le_health_check_reste_accessible_sans_authentification(): void
    {
        $reponse = $this->getJson('/api/health');

        // 200 quand tout repond, 503 quand un service est en panne (Redis
        // n'est pas forcement lance sur un poste de developpement). Dans les
        // deux cas, ce n'est ni 401 ni 403 : la sonde doit pouvoir appeler.
        $this->assertContains($reponse->status(), [200, 503]);
        $reponse->assertJsonStructure(['status', 'services', 'timestamp']);
    }

    public function test_chaque_service_ne_rapporte_que_ok_ou_failed(): void
    {
        $services = $this->getJson('/api/health')->json('services');

        $this->assertNotEmpty($services);

        foreach ($services as $nom => $etat) {
            $this->assertContains(
                $etat,
                ['ok', 'failed'],
                "Le service {$nom} rapporte '{$etat}' : seuls 'ok' et 'failed' sont autorises."
            );
        }
    }

    /**
     * Le test qui compte : quand la base tombe, la reponse ne doit pas
     * recopier le message de l'exception. Celui-ci contient le pilote, le
     * chemin ou l'hote, le port et l'utilisateur de la base.
     */
    public function test_une_base_indisponible_ne_divulgue_pas_le_detail_de_la_panne(): void
    {
        $cheminRevelateur = '/repertoire-inexistant-vitalis/base-secrete.sqlite';

        config(['database.connections.sqlite.database' => $cheminRevelateur]);
        DB::purge('sqlite');

        $reponse = $this->getJson('/api/health');

        $reponse->assertJsonPath('services.database', 'failed');

        // Ni le chemin, ni le nom du pilote, ni un extrait d'exception.
        $corps = $reponse->getContent();
        $this->assertStringNotContainsString($cheminRevelateur, $corps);
        $this->assertStringNotContainsString('base-secrete', $corps);
        $this->assertStringNotContainsString('SQLSTATE', $corps);
        $this->assertStringNotContainsString('PDO', $corps);
        $this->assertStringNotContainsString('failed:', $corps);
    }

    /**
     * La reponse nominale ne doit pas non plus enumerer la configuration
     * (version de PHP, de Laravel, variables d'environnement...) : la sonde
     * n'a besoin que d'un etat par service.
     */
    public function test_le_health_check_nexpose_pas_la_configuration(): void
    {
        $corps = $this->getJson('/api/health')->getContent();

        foreach (['APP_KEY', 'DB_PASSWORD', 'password', 'version', 'env'] as $terme) {
            $this->assertStringNotContainsStringIgnoringCase($terme, $corps);
        }
    }
}
