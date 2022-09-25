<?php

namespace Kanboard\Plugin\KanboardFocalboardExporter\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Model\ColumnModel;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\TagModel;
use Kanboard\Model\TaskExternalLinkModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\TaskTagModel;
use Kanboard\Model\UserModel;

class FocalboardController extends BaseController
{
    public function export()
    {
        $project = $this->getProject();

        if ($this->request->isPost()) {
            $filename = $this->getFileName($project);
            $data = $this->getData($project);
            $this->response->withFileDownload($filename);
            $this->response->json($data);
        } else {
            $this->response->html($this->template->render('KanboardFocalboardExporter:export/form', [
                'values'  => [
                    'project_id' => $project['id'],
                    'from' => 1,
                    'to' => 1,
                ],
                'project' => $project,
            ]));
        }
    }

    private function getFileName($project)
    {
        $name = strtolower(preg_replace("/\W+/", "-", $project["name"]));
        return "$name.json";
    }

    private function getData($project)
    {
        $tags = $this->getTags($project);
        return [
            "id" => $project["id"],
            "name" => $project["name"],
            "description" => $project["description"],
            "columns" => $this->getColumns($project),
            "tags" => $tags,
            "tasks" => $this->getTasks(
                $project,
                $this->getGroupedSubtasks($project),
                $this->getGroupedTags($project, $tags),
                $this->getGroupedLinks($project)
            ),
        ];
    }

    private function getColumns($project)
    {
        return $this->db
            ->table(ColumnModel::TABLE)
            ->columns('id', 'title', 'position', 'description')
            ->eq('project_id', $project["id"])
            ->findAll();
    }

    private function getTags($project)
    {
        return $this->db
            ->table(TagModel::TABLE)
            ->columns('id', 'name')
            ->eq('project_id', $project["id"])
            ->findAll();
    }

    private function getTasks(
        $project,
        $subTasks,
        $tags,
        $links
    ) {
        $tt = TaskModel::TABLE;
        $tasks = $this->db
            ->table($tt)
            ->columns(
                "$tt.id",
                "$tt.title",
                "$tt.description",
                "$tt.column_id",
                "$tt.is_active",
                "$tt.date_creation",
                "$tt.date_due",
                "cu.email creator_email",
                "ou.email assignee_email"
            )
            ->join(UserModel::TABLE . " ou", 'id', 'owner_id', '', 'ou')
            ->join(UserModel::TABLE . " cu", 'id', 'creator_id', '', 'cu')
            ->eq($tt . '.project_id', $project["id"])
            ->asc($tt . '.id')
            ->findAll();

        foreach ($tasks as $i => $task) {
            $tasks[$i] = array_merge($task, [
                "sub_tasks" => $subTasks[$task["id"]] ?? [],
                "tags" => $tags[$task["id"]] ?? [],
                "links" => $links[$task["id"]] ?? [],
            ]);
        }
        return $tasks;
    }

    private function makeTagMap($tags)
    {
        $grouped = [];
        foreach ($tags as $tags) {
            $grouped[$tags["id"]][] = $tags;
        }
        return $grouped;
    }

    private function getGroupedTags($project, $tags)
    {
        $map = $this->makeTagMap($tags);
        $tt = TaskTagModel::TABLE;
        $tags = $this->db->table($tt)
            ->eq('project_id', $project["id"])
            ->columns(
                "$tt.*",
            )
            ->join(TaskModel::TABLE, 'id', 'task_id')
            ->findAll();

        $grouped = [];
        foreach ($tags as $tag) {
            if (array_key_exists($tag["tag_id"], $map)) {
                $grouped[$tag["task_id"]][] = $map[$tag["tag_id"]];
            }
        }
        return $grouped;
    }

    private function getGroupedSubtasks($project)
    {
        $st = SubtaskModel::TABLE;
        $subTasks = $this->db->table($st)
            ->eq('project_id', $project["id"])
            ->columns(
                "$st.id",
                "$st.title",
                "$st.position",
                "$st.task_id",
                UserModel::TABLE . ".email assignee_email"
            )
            ->join(TaskModel::TABLE, 'id', 'task_id')
            ->join(UserModel::TABLE, 'id', 'user_id')
            ->asc($st . '.id')
            ->findAll();

        $grouped = [];
        foreach ($subTasks as $subTask) {
            $grouped[$subTask["task_id"]][] = $subTask;
        }
        return $grouped;
    }

    private function getGroupedLinks($project)
    {
        $tel = TaskExternalLinkModel::TABLE;
        $links = $this
            ->db
            ->table($tel)
            ->eq('project_id', $project["id"])
            ->columns("$tel.title", "$tel.url", "$tel.task_id")
            ->join(TaskModel::TABLE, 'id', 'task_id')
            ->asc("$tel.id")
            ->findAll();

        $grouped = [];
        foreach ($links as $link) {
            $grouped[$link["task_id"]][] = $link;
        }
        return $grouped;
    }
}
