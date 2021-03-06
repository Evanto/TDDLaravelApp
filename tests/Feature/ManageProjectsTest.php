<?php

namespace Tests\Feature;

use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Facades\Tests\Setup\ProjectFactory;

class ManageProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function guest_caclennot_manage_projects()
    {

        $project = factory('App\Project')->create();

        $this->post('/projects', $project->toArray())->assertRedirect('login');
        $this->get('/projects/create')->assertRedirect('login');
        $this->get('/projects/edit')->assertRedirect('login');
        $this->get('/projects')->assertRedirect('login');
        $this->get($project->path())->assertRedirect('login');
    }

    public function a_user_can_create_a_project()
    {
        $this->signIn();

        $this->get('/projects/create')->assertStatus(200);

        $this->followingRedirects()
            ->post('/projects', $attributes = factory(Project::class)->raw())
            ->assertSee($attributes['title'])
            ->assertSee($attributes['description'])
            ->assertSee($attributes['notes']);
    }

    /** @test */
    public function a_user_can_update_a_project()
    {

        $project = ProjectFactory::create();

        $this->actingAs($project->owner)->
        patch($project->path(), $attributes = ['title' => 'Changed',
            'description' => 'Changed',
            'notes' => 'Changed'])->
        assertRedirect($project->path());

        $this->get($project->path().'/edit')->assertOk();

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function a_user_can_update_a_project_general_notes(){

        $project = ProjectFactory::create();

        $this->actingAs($project->owner)
            ->patch($project->path(), $attributes = ['notes' => 'Changed'])->
        assertRedirect($project->path());

        $this->get($project->path().'/edit')->assertOk();

        $this->assertDatabaseHas('projects', $attributes);

    }

    /** @test */
    public function a_user_can_view_a_their_project()
    {
        $project = ProjectFactory::create();
        $this->actingAs($project->owner)->
        get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->descriprion);
    }

    /** @test */
    public function a_authenticated_user_cannot_view_a_projects_of_others()
    {
        // $this->withoutExceptionHandling();
        $this->signIn();
        $project = factory('App\Project')->create();
        $this->get($project->path())->assertstatus(403);
    }

    /** @test */
    public function a_authenticated_user_cannot_update_a_projects_of_others()
    {
        // $this->withoutExceptionHandling();
        $this->signIn();
        $project = factory('App\Project')->create();
        $this->get($project->path())->assertstatus(403);
    }

    /** @test */
    public function a_project_requiest_a_title()
    {
        $this->signIn();
        $attributes = factory('App\Project')->raw(['title' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('title');

    }

    /** @test */
    public function a_project_requiest_a_description()
    {
        $this->signIn();
        $attributes = factory('App\Project')->raw(['description' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('description');

    }

    /** @test */
    function unauthorized_users_cannot_delete_projects()
    {
        $project = ProjectFactory::create();

        $this->delete($project->path())
            ->assertRedirect('/login');

        $user = $this->signIn();

        $this->delete($project->path())->assertStatus(403);

        $project->invite($user);

        $this->actingAs($user)->delete($project->path())->assertStatus(403);
    }

    /** @test */
    function a_user_can_delete_a_project()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner)
            ->delete($project->path())
            ->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', $project->only('id'));
    }

    /** @test */
    function a_user_can_see_all_projects_they_have_been_invited_to_on_their_dashboard()
    {
        $project = tap(ProjectFactory::create())->invite($this->signIn());

        $this->get('/projects')->assertSee($project->title);
    }

}
