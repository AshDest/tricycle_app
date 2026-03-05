<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class SystemLogs extends Component
{
    public $logFiles = [];
    public $selectedFile = '';
    public $logContent = '';
    public $logLines = 100;
    public $search = '';

    public function mount()
    {
        $this->loadLogFiles();

        if (count($this->logFiles) > 0) {
            $this->selectedFile = $this->logFiles[0]['name'];
            $this->loadLogContent();
        }
    }

    public function loadLogFiles()
    {
        $logsPath = storage_path('logs');
        $files = [];

        if (is_dir($logsPath)) {
            foreach (glob($logsPath . '/*.log') as $file) {
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatBytes(filesize($file)),
                    'modified' => Carbon::createFromTimestamp(filemtime($file))->format('d/m/Y H:i'),
                ];
            }
        }

        // Trier par date décroissante
        usort($files, function($a, $b) {
            return filemtime($b['path']) - filemtime($a['path']);
        });

        $this->logFiles = $files;
    }

    public function loadLogContent()
    {
        if (empty($this->selectedFile)) {
            $this->logContent = '';
            return;
        }

        $filePath = storage_path('logs/' . $this->selectedFile);

        if (!file_exists($filePath)) {
            $this->logContent = 'Fichier non trouvé.';
            return;
        }

        // Lire les dernières lignes du fichier
        $lines = $this->tailFile($filePath, $this->logLines);

        if (!empty($this->search)) {
            $lines = array_filter($lines, function($line) {
                return stripos($line, $this->search) !== false;
            });
        }

        $this->logContent = implode("\n", $lines);
    }

    public function tailFile($filepath, $lines = 100)
    {
        $content = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($content, -$lines);
    }

    public function selectFile($filename)
    {
        $this->selectedFile = $filename;
        $this->loadLogContent();
    }

    public function updatedLogLines()
    {
        $this->loadLogContent();
    }

    public function updatedSearch()
    {
        $this->loadLogContent();
    }

    public function clearLog()
    {
        $filePath = storage_path('logs/' . $this->selectedFile);

        if (file_exists($filePath)) {
            file_put_contents($filePath, '');
            $this->logContent = '';
            $this->loadLogFiles();
            session()->flash('success', 'Fichier de log vidé.');
        }
    }

    public function deleteLog()
    {
        $filePath = storage_path('logs/' . $this->selectedFile);

        if (file_exists($filePath) && $this->selectedFile !== 'laravel.log') {
            unlink($filePath);
            $this->loadLogFiles();

            if (count($this->logFiles) > 0) {
                $this->selectedFile = $this->logFiles[0]['name'];
                $this->loadLogContent();
            } else {
                $this->selectedFile = '';
                $this->logContent = '';
            }

            session()->flash('success', 'Fichier supprimé.');
        } else {
            session()->flash('error', 'Impossible de supprimer ce fichier.');
        }
    }

    public function downloadLog()
    {
        $filePath = storage_path('logs/' . $this->selectedFile);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        session()->flash('error', 'Fichier non trouvé.');
        return null;
    }

    public function refreshLogs()
    {
        $this->loadLogContent();
        $this->dispatch('$refresh');
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function render()
    {
        return view('livewire.super-admin.system-logs');
    }
}

