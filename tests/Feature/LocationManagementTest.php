<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Category;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_access_and_create_buildings_and_rooms(): void
    {
        $student = User::factory()->student()->create();
        $building = Building::factory()->create();

        // Guests redirected to login
        $this->get(route('admin.locations.index'))->assertRedirect(route('login'));
        $this->post(route('admin.buildings.store'), ['name' => 'Guest Hall'])->assertRedirect(route('login'));
        $this->post(route('admin.locations.store'), [
            'building_id' => $building->id,
            'room_or_area' => 'Room 101',
        ])->assertRedirect(route('login'));

        // Students blocked with 403
        $this->actingAs($student)->get(route('admin.locations.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.buildings.store'), ['name' => 'Student Hall'])->assertForbidden();
        $this->actingAs($student)->post(route('admin.locations.store'), [
            'building_id' => $building->id,
            'room_or_area' => 'Room 101',
        ])->assertForbidden();

        $this->assertDatabaseMissing('buildings', ['name' => 'Student Hall']);
        $this->assertDatabaseCount('locations', 0);
    }

    public function test_admin_can_add_building_and_room_and_student_can_report_issue_there(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();

        // Admin adds a new building
        $this->actingAs($admin)
            ->post(route('admin.buildings.store'), [
                'name' => 'Innovation Hub',
                'code' => 'ih-1',
            ])
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $building = Building::where('name', 'Innovation Hub')->firstOrFail();
        $this->assertSame('IH-1', $building->code);

        // Admin adds a room inside the new building
        $this->actingAs($admin)
            ->post(route('admin.locations.store'), [
                'building_id' => $building->id,
                'room_or_area' => 'Robotics Lab 204',
                'floor' => '2nd Floor',
            ])
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $location = Location::where('building_id', $building->id)
            ->where('room_or_area', 'Robotics Lab 204')
            ->firstOrFail();
        $this->assertSame('Innovation Hub — Robotics Lab 204', $location->name);

        // Directory page displays both building and room
        $this->actingAs($admin)
            ->get(route('admin.locations.index'))
            ->assertOk()
            ->assertSee('Innovation Hub')
            ->assertSee('IH-1')
            ->assertSee('Robotics Lab 204');

        // Student sees the new building and room in Report Issue form and can submit an issue
        $this->actingAs($student)
            ->get(route('student.issues.create'))
            ->assertOk()
            ->assertSee('Innovation Hub')
            ->assertSee('Robotics Lab 204');

        $this->actingAs($student)
            ->post(route('student.issues.store'), [
                'title' => 'Soldering station power outlet dead',
                'category_id' => $category->id,
                'building_id' => $building->id,
                'location_id' => $location->id,
                'description' => 'The wall power strip near the robotics soldering bench stopped supplying power this morning.',
                'accuracy_confirmed' => '1',
            ])
            ->assertRedirect();

        $issue = Issue::firstOrFail();
        $this->assertSame($location->id, $issue->location_id);
        $this->assertSame('Innovation Hub — Robotics Lab 204', $issue->location->name);
    }

    public function test_building_and_room_validation_blocks_duplicates_but_allows_same_room_in_different_buildings(): void
    {
        $admin = User::factory()->admin()->create();
        $buildingA = Building::factory()->create(['name' => 'Science Block', 'code' => 'SB']);
        $buildingB = Building::factory()->create(['name' => 'Engineering Block', 'code' => 'EB']);

        // Case-insensitive duplicate building name and code rejected
        $this->actingAs($admin)
            ->post(route('admin.buildings.store'), [
                'name' => '  science block  ',
                'code' => 'sb',
            ])
            ->assertSessionHasErrors(['name', 'code']);

        // Add Room 101 to Science Block
        $this->actingAs($admin)
            ->post(route('admin.locations.store'), [
                'building_id' => $buildingA->id,
                'room_or_area' => 'Room 101',
                'floor' => '1st Floor',
            ])
            ->assertRedirect(route('admin.locations.index'));

        // Case-insensitive duplicate Room 101 in the SAME building rejected
        $this->actingAs($admin)
            ->post(route('admin.locations.store'), [
                'building_id' => $buildingA->id,
                'room_or_area' => 'room 101',
            ])
            ->assertSessionHasErrors('room_or_area');

        // Same room name ("Room 101") in a DIFFERENT building is allowed
        $this->actingAs($admin)
            ->post(route('admin.locations.store'), [
                'building_id' => $buildingB->id,
                'room_or_area' => 'Room 101',
                'floor' => '1st Floor',
            ])
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('locations', ['name' => 'Science Block — Room 101']);
        $this->assertDatabaseHas('locations', ['name' => 'Engineering Block — Room 101']);
    }

    public function test_student_cannot_submit_room_that_belongs_to_a_different_building(): void
    {
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();
        $buildingA = Building::factory()->create(['name' => 'Building Alpha']);
        $buildingB = Building::factory()->create(['name' => 'Building Beta']);
        $roomInB = Location::factory()->create([
            'building_id' => $buildingB->id,
            'building' => $buildingB->name,
            'room_or_area' => 'Room B-202',
            'name' => 'Building Beta — Room B-202',
        ]);

        // Mismatched building_id (A) + location_id (from B) must be rejected server-side
        $this->actingAs($student)
            ->post(route('student.issues.store'), [
                'title' => 'Mismatched building and room test',
                'category_id' => $category->id,
                'building_id' => $buildingA->id,
                'location_id' => $roomInB->id,
                'description' => 'Trying to pair Building Alpha with a room that actually belongs to Building Beta.',
                'accuracy_confirmed' => '1',
            ])
            ->assertSessionHasErrors('location_id');

        $this->assertDatabaseCount('issues', 0);
    }
}
