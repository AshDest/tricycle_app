<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Models\SystemSetting;

#[Layout('components.layouts.dashlite')]
class EmailSettings extends Component
{
    use WithPagination;

    public $activeTab = 'config';

    // Configuration éditable
    public $mailMailer = 'smtp';
    public $mailHost = '';
    public $mailPort = '587';
    public $mailUsername = '';
    public $mailPassword = '';
    public $mailEncryption = 'tls';
    public $mailFromAddress = '';
    public $mailFromName = '';
    public $emailsActifs = true;

    // Test email
    public $testEmail = '';
    public $testSubject = 'Test Tricycle App';
    public $testMessage = 'Ceci est un email de test envoyé depuis Tricycle App.';

    // Filtres jobs
    public $filterStatus = '';
    public $filterQueue = '';

    public function mount()
    {
        // Charger la configuration depuis SystemSetting ou .env
        $this->mailMailer = SystemSetting::get('mail_mailer', config('mail.default', 'smtp'));
        $this->mailHost = SystemSetting::get('mail_host', config('mail.mailers.smtp.host', ''));
        $this->mailPort = SystemSetting::get('mail_port', config('mail.mailers.smtp.port', '587'));
        $this->mailUsername = SystemSetting::get('mail_username', config('mail.mailers.smtp.username', ''));
        $this->mailPassword = ''; // Ne jamais charger le mot de passe pour sécurité
        $this->mailEncryption = SystemSetting::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $this->mailFromAddress = SystemSetting::get('mail_from_address', config('mail.from.address', ''));
        $this->mailFromName = SystemSetting::get('mail_from_name', config('mail.from.name', 'Tricycle App'));
        $this->emailsActifs = (bool) SystemSetting::get('emails_actifs', true);

        // Email de test par défaut = email de l'admin connecté
        $this->testEmail = auth()->user()->email;
    }

    /**
     * Sauvegarder la configuration email
     */
    public function sauvegarderConfiguration()
    {
        $this->validate([
            'mailMailer' => 'required|in:smtp,log,sendmail',
            'mailHost' => 'required_if:mailMailer,smtp|nullable|string|max:255',
            'mailPort' => 'required_if:mailMailer,smtp|nullable|numeric',
            'mailUsername' => 'nullable|string|max:255',
            'mailEncryption' => 'nullable|in:tls,ssl,null',
            'mailFromAddress' => 'required|email|max:255',
            'mailFromName' => 'required|string|max:255',
        ], [
            'mailHost.required_if' => 'Le serveur SMTP est requis pour le mode SMTP.',
            'mailPort.required_if' => 'Le port est requis pour le mode SMTP.',
            'mailFromAddress.required' => 'L\'adresse d\'expédition est requise.',
            'mailFromAddress.email' => 'L\'adresse d\'expédition doit être un email valide.',
            'mailFromName.required' => 'Le nom d\'expédition est requis.',
        ]);

        try {
            // Sauvegarder dans SystemSettings
            SystemSetting::set('mail_mailer', $this->mailMailer, 'string', 'mail');
            SystemSetting::set('mail_host', $this->mailHost, 'string', 'mail');
            SystemSetting::set('mail_port', $this->mailPort, 'string', 'mail');
            SystemSetting::set('mail_username', $this->mailUsername, 'string', 'mail');

            // Sauvegarder le mot de passe seulement s'il est fourni
            if (!empty($this->mailPassword)) {
                SystemSetting::set('mail_password', encrypt($this->mailPassword), 'encrypted', 'mail');
            }

            SystemSetting::set('mail_encryption', $this->mailEncryption, 'string', 'mail');
            SystemSetting::set('mail_from_address', $this->mailFromAddress, 'string', 'mail');
            SystemSetting::set('mail_from_name', $this->mailFromName, 'string', 'mail');
            SystemSetting::set('emails_actifs', $this->emailsActifs, 'boolean', 'mail');

            // Logger l'action
            Log::info('Configuration email mise à jour', [
                'by' => auth()->user()->email,
                'mailer' => $this->mailMailer,
            ]);

            session()->flash('success', 'Configuration email sauvegardée avec succès !');

            // Réinitialiser le mot de passe affiché
            $this->mailPassword = '';

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
            Log::error('Erreur sauvegarde config email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Activer/désactiver les emails
     */
    public function toggleEmails()
    {
        $this->emailsActifs = !$this->emailsActifs;
        SystemSetting::set('emails_actifs', $this->emailsActifs, 'boolean', 'mail');

        $status = $this->emailsActifs ? 'activés' : 'désactivés';
        session()->flash('success', "Les emails ont été {$status}");

        Log::info("Emails {$status}", ['by' => auth()->user()->email]);
    }

    /**
     * Réinitialiser à la configuration .env
     */
    public function reinitialiserConfiguration()
    {
        try {
            // Supprimer les paramètres de la base de données
            SystemSetting::where('group', 'mail')->delete();

            // Recharger depuis .env
            $this->mailMailer = config('mail.default', 'smtp');
            $this->mailHost = config('mail.mailers.smtp.host', '');
            $this->mailPort = config('mail.mailers.smtp.port', '587');
            $this->mailUsername = config('mail.mailers.smtp.username', '');
            $this->mailPassword = '';
            $this->mailEncryption = config('mail.mailers.smtp.encryption', 'tls');
            $this->mailFromAddress = config('mail.from.address', '');
            $this->mailFromName = config('mail.from.name', 'Tricycle App');
            $this->emailsActifs = true;

            session()->flash('success', 'Configuration réinitialisée aux valeurs par défaut du serveur');

            Log::info('Configuration email réinitialisée', ['by' => auth()->user()->email]);

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un email de test
     */
    public function envoyerTestEmail()
    {
        $this->validate([
            'testEmail' => 'required|email',
            'testSubject' => 'required|string|max:255',
            'testMessage' => 'required|string',
        ]);

        try {
            // Appliquer la configuration depuis la BDD avant l'envoi
            $this->appliquerConfigurationPourEnvoi();

            Mail::raw($this->testMessage, function ($message) {
                $message->to($this->testEmail)
                    ->subject($this->testSubject);
            });

            session()->flash('success', "Email de test envoyé avec succès à {$this->testEmail}");

            Log::info('Email de test envoyé', [
                'to' => $this->testEmail,
                'by' => auth()->user()->email,
            ]);
        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de l'envoi : " . $e->getMessage());
            Log::error('Erreur envoi email test', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Appliquer la configuration pour l'envoi
     */
    protected function appliquerConfigurationPourEnvoi()
    {
        $mailer = SystemSetting::get('mail_mailer', config('mail.default'));
        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', SystemSetting::get('mail_host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', SystemSetting::get('mail_port', config('mail.mailers.smtp.port')));
            Config::set('mail.mailers.smtp.username', SystemSetting::get('mail_username', config('mail.mailers.smtp.username')));

            // Décrypter le mot de passe
            $password = SystemSetting::get('mail_password');
            if ($password) {
                try {
                    $password = decrypt($password);
                } catch (\Exception $e) {
                    $password = config('mail.mailers.smtp.password');
                }
            } else {
                $password = config('mail.mailers.smtp.password');
            }
            Config::set('mail.mailers.smtp.password', $password);

            $encryption = SystemSetting::get('mail_encryption', config('mail.mailers.smtp.encryption'));
            Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
        }

        Config::set('mail.from.address', SystemSetting::get('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', SystemSetting::get('mail_from_name', config('mail.from.name')));
    }

    /**
     * Obtenir les jobs en attente
     */
    public function getJobsEnAttente()
    {
        return DB::table('jobs')
            ->when($this->filterQueue, fn($q) => $q->where('queue', $this->filterQueue))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'jobsPage');
    }

    /**
     * Obtenir les jobs échoués
     */
    public function getJobsEchoues()
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(10, ['*'], 'failedPage');
    }

    /**
     * Obtenir les statistiques des emails
     */
    public function getStatistiques()
    {
        $jobsEnAttente = DB::table('jobs')->count();
        $jobsEchoues = DB::table('failed_jobs')->count();

        $notificationsAujourdhui = DB::table('system_notifications')
            ->whereDate('created_at', today())
            ->count();

        $notificationsCetteSemaine = DB::table('system_notifications')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            'jobs_en_attente' => $jobsEnAttente,
            'jobs_echoues' => $jobsEchoues,
            'notifications_aujourdhui' => $notificationsAujourdhui,
            'notifications_semaine' => $notificationsCetteSemaine,
        ];
    }

    /**
     * Relancer un job échoué
     */
    public function relancerJob($jobId)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$jobId]]);
            session()->flash('success', 'Job relancé avec succès');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Relancer tous les jobs échoués
     */
    public function relancerTousJobs()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            session()->flash('success', 'Tous les jobs ont été relancés');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un job échoué
     */
    public function supprimerJob($jobId)
    {
        try {
            DB::table('failed_jobs')->where('id', $jobId)->delete();
            session()->flash('success', 'Job supprimé');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vider tous les jobs échoués
     */
    public function viderJobsEchoues()
    {
        try {
            Artisan::call('queue:flush');
            session()->flash('success', 'Tous les jobs échoués ont été supprimés');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier la configuration email
     */
    public function verifierConfiguration()
    {
        $errors = [];

        if ($this->mailMailer === 'smtp') {
            if (empty($this->mailHost)) {
                $errors[] = 'Serveur SMTP non configuré';
            }
            if (empty($this->mailPort)) {
                $errors[] = 'Port SMTP non configuré';
            }
        }

        if (empty($this->mailFromAddress)) {
            $errors[] = 'Adresse d\'expédition non configurée';
        }

        if ($this->mailMailer === 'log') {
            $errors[] = 'Mode LOG activé - les emails ne sont pas réellement envoyés';
        }

        if (!$this->emailsActifs) {
            $errors[] = 'Les emails sont désactivés';
        }

        if (empty($errors)) {
            session()->flash('success', 'Configuration email valide ✓');
        } else {
            session()->flash('warning', 'Problèmes détectés: ' . implode(', ', $errors));
        }
    }

    /**
     * Statut du worker
     */
    public function getWorkerStatus()
    {
        $lastProcessed = DB::table('jobs')
            ->where('available_at', '<', now()->timestamp)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastProcessed ? 'actif' : 'inactif ou en attente';
    }

    /**
     * Vérifier si la config vient de la BDD
     */
    public function isConfigFromDatabase()
    {
        return SystemSetting::where('group', 'mail')->exists();
    }

    /**
     * Vérifier si un mot de passe est configuré
     */
    public function hasPassword()
    {
        return !empty(SystemSetting::get('mail_password'));
    }

    public function render()
    {
        return view('livewire.super-admin.email-settings', [
            'stats' => $this->getStatistiques(),
            'jobsEnAttente' => $this->getJobsEnAttente(),
            'jobsEchoues' => $this->getJobsEchoues(),
            'workerStatus' => $this->getWorkerStatus(),
            'configFromDatabase' => $this->isConfigFromDatabase(),
            'hasPassword' => $this->hasPassword(),
        ]);
    }
}

