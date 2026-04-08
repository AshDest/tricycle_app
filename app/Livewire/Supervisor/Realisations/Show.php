<?php
namespace App\Livewire\Supervisor\Realisations;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Realisation;
use App\Services\MediaService;
#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Realisation $realisation;
    public function mount(Realisation $realisation)
    {
        $this->realisation = $realisation->load('createdBy');
    }
    public function togglePublish()
    {
        $this->realisation->update(['is_published' => !$this->realisation->is_published]);
        $this->realisation->refresh();
    }
    public function deleteMedia(int $index)
    {
        $media = $this->realisation->media ?? [];
        if (isset($media[$index])) {
            $mediaService = new MediaService();
            $mediaService->deleteMedia($media[$index]);
            unset($media[$index]);
            $this->realisation->update(['media' => array_values($media)]);
            $this->realisation->refresh();
        }
    }
    public function delete()
    {
        $mediaService = new MediaService();
        $mediaService->deleteAllMedia($this->realisation->media ?? []);
        $this->realisation->forceDelete();
        return redirect()->route('supervisor.realisations.index');
    }
    public function render()
    {
        return view('livewire.supervisor.realisations.show');
    }
}
