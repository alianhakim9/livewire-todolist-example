<?php

namespace App\Livewire;

use App\Models\Todo;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name = '';

    public $searchQuery;

    public $editingTodoId;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create()
    {
        // validate
        $validated = $this->validateOnly('name');

        // create todo
        Todo::create($validated);

        // clear input
        $this->reset('name');

        // send flash message
        request()->session()->flash('success', 'Created.');

        $this->resetPage();
    }

    public function delete($todoId)
    {
        try {
            Todo::findOrFail($todoId)->delete();
        } catch (\Exception $e) {
            // Log::error('delete', $e->getMessage());
            session()->flash('error', 'Failed to delete todo!');
            return;
        }
    }

    public function toggle($todoId)
    {
        $todo = Todo::find($todoId);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($todoId)
    {
        $this->editingTodoId = $todoId;
        $this->editingTodoName = Todo::find($todoId)->name;
    }

    public function cancelEdit()
    {
        $this->reset('editingTodoId', 'editingTodoName');
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');

        Todo::find($this->editingTodoId)->update([
            'name' => $this->editingTodoName
        ]);

        $this->cancelEdit();
    }

    public function render()
    {
        $todos = Todo::latest()->where('name', 'like', "%$this->searchQuery%")->paginate(5);

        return view('livewire.todo-list', [
            'todos' => $todos
        ]);
    }
}
