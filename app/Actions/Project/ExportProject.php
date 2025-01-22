<?php

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportProject
{
    public function export(Project $project)
    {
        $project->load(['media', 'memberInvites', 'memberInvites.profile', 'locations', 'owner']);

        $disk = Storage::build(storage_path('app/public/project_exports'));

        $project_dir = $project->id . '/';
        $disk->makeDirectory($project_dir);
        $attachments = $this->getProjectAttachments($project);

        $pdf_path = $project_dir . 'index.pdf';
        $this->generatePdf($disk->path($pdf_path), $project, $attachments);

        $archive_path = $project_dir . 'archive.zip';
        $disk->delete($archive_path);
        $this->composeZip($disk->path($archive_path), $disk->path($pdf_path), $attachments);
        $disk->delete($pdf_path);

        return $disk->download($archive_path, 'project.zip');
    }

    private function generatePdf($path, $project, $files)
    {

        $html = view('pdf.project', ['project' => $project, 'files' => $files], [])->render();

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => storage_path('tmp'),
            'margin_left' => 15,
            'margin_right' => 05,
            'margin_top' => 20,
            'margin_bottom' => 15,
            'margin_header' => 05,
            'margin_footer' => 05,
        ]);
        $mpdf->WriteHTML($html);
        return $mpdf->Output($path);
    }

    private function composeZip($path, $pdf, $filesByType)
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE) !== TRUE) {
            exit("cannot open <$path>\n");
        }
        $zip->addFile($pdf, '/Проект.pdf');
        $zip->addEmptyDir('Приложения');
        foreach ($filesByType as $type => $files) {
            if (!$files) continue;
            foreach ($files as $file) {
                $zip->addFile($file['model']->getPath(), '/Приложения/' . $file['name']);
            }
        }
        $zip->close();
    }

    private function getProjectAttachments($project): array
    {
        $fileNames = [
            Project::EXTENDED_SYNOPSIS_MEDIA => 'Расширенный_синопсис',
            Project::ATTACHMENTS_MEDIA => 'Приложение',
            Project::COSTUMES_MEDIA => 'Референс_по_костюмам',
            Project::MAKEUP_MEDIA => 'Референс_по_гриму',
            Project::CAST_MEDIA => 'Референс_по_актерам',
            Project::DECORATIONS_MEDIA => 'Референс_по_декорациям',
            Project::LOCATIONS_MEDIA => 'Референс_по_локациям',
            Project::FINANCIAL_PLAN_MEDIA => 'Смета',
            Project::FINANCIAL_PROOF_MEDIA => 'Подтверждение_сметы',
            Project::PARTNERSHIP_PROOF_MEDIA => 'Партнерское_письмо',
        ];
        $files = [];
        foreach (Project::MEDIA_TYPES as $mediaType) {
            $mediaWithType = collect($project->media->where('collection_name', $mediaType));
            $i = 1;
            $files[$mediaType] = [];
            foreach ($mediaWithType as $media) {
                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                $fileName = $fileNames[$mediaType];
                $name = $mediaWithType->count() > 1 ? "{$fileName}_{$i}.{$extension}" : "{$fileName}.{$extension}";
                $files[$mediaType][] = ['key' => $i++, 'count' => $mediaWithType->count(), 'name' => $name, 'model' => $media];
            }
            if (count($files[$mediaType]) === 0) $files[$mediaType] = false;
        }
        return $files;
    }
}
