<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Ability
 *
 * @property-read \App\Models\Resource|null $resource
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ability query()
 */
	class Ability extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AbilityRole
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AbilityRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbilityRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbilityRole query()
 */
	class AbilityRole extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Address
 *
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BaseModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel withoutTrashed()
 */
	class BaseModel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Checklist
 *
 * @property int $id
 * @property string $level
 * @property string $situation
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $kid_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competence> $competences
 * @property-read int|null $competences_count
 * @property-read mixed $situation_label
 * @property-read \App\Models\Kid|null $kid
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plane> $planes
 * @property-read int|null $planes_count
 * @method static \Database\Factories\ChecklistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist query()
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereKidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereSituation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Checklist withoutTrashed()
 */
	class Checklist extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChecklistCompetence
 *
 * @property int $checklist_id
 * @property int $competence_id
 * @property int $note
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence whereChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence whereCompetenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChecklistCompetence whereNote($value)
 */
	class ChecklistCompetence extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Competence
 *
 * @property int $id
 * @property int|null $level_id
 * @property int|null $domain_id
 * @property int $code
 * @property string $description
 * @property string $description_detail
 * @property int|null $percentil_25
 * @property int|null $percentil_50
 * @property int|null $percentil_75
 * @property int|null $percentil_90
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checklist> $checklists
 * @property-read int|null $checklists_count
 * @property-read \App\Models\Domain|null $domain
 * @property-read \App\Models\Level|null $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plane> $planes
 * @property-read int|null $planes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Competence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competence query()
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereDescriptionDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereDomainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence wherePercentil25($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence wherePercentil50($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence wherePercentil75($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence wherePercentil90($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competence whereUpdatedAt($value)
 */
	class Competence extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CompetencePlane
 *
 * @property int $plane_id
 * @property int $competence_id
 * @method static \Illuminate\Database\Eloquent\Builder|CompetencePlane newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompetencePlane newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompetencePlane query()
 * @method static \Illuminate\Database\Eloquent\Builder|CompetencePlane whereCompetenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompetencePlane wherePlaneId($value)
 */
	class CompetencePlane extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Domain
 *
 * @property int $id
 * @property string $name
 * @property string $initial
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Level> $levels
 * @property-read int|null $levels_count
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain query()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereUpdatedAt($value)
 */
	class Domain extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DomainLevel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DomainLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DomainLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DomainLevel query()
 */
	class DomainLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Kid
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $gender
 * @property string|null $ethnicity
 * @property \Illuminate\Support\Carbon $birth_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $responsible_id
 * @property string|null $photo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checklist> $checklists
 * @property-read int|null $checklists_count
 * @property-read \App\Models\Checklist|null $currentChecklist
 * @property-read mixed $age
 * @property-read mixed $full_name_months
 * @property-read mixed $initials
 * @property-read mixed $months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plane> $planes
 * @property-read int|null $planes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Professional> $professionals
 * @property-read int|null $professionals_count
 * @property-read \App\Models\User|null $responsible
 * @method static \Database\Factories\KidFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Kid forProfessional()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereEthnicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereResponsibleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kid withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Kid withoutTrashed()
 */
	class Kid extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Level
 *
 * @property int $id
 * @property string $level
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Domain> $domains
 * @property-read int|null $domains_count
 * @method static \Illuminate\Database\Eloquent\Builder|Level newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Level newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Level query()
 * @method static \Illuminate\Database\Eloquent\Builder|Level whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Level whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Level whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Level whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Level whereUpdatedAt($value)
 */
	class Level extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Log
 *
 * @property int $id
 * @property string|null $object
 * @property int|null $object_id
 * @property string $action
 * @property string $description
 * @property string $creation_date
 * @property int|null $created_by
 * @property string|null $modification_date
 * @property int|null $modified_by
 * @property string|null $removal_date
 * @property int|null $removed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereModificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereObject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereRemovalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereRemovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Log withoutTrashed()
 */
	class Log extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Plane
 *
 * @property int $id
 * @property int|null $kid_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $checklist_id
 * @property string|null $name
 * @property int $is_active
 * @property-read \App\Models\Checklist $checklist
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competence> $competences
 * @property-read int|null $competences_count
 * @property-read \App\Models\Kid|null $kid
 * @method static \Illuminate\Database\Eloquent\Builder|Plane newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plane newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plane onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Plane query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereKidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plane withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Plane withoutTrashed()
 */
	class Plane extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Professional
 *
 * @property int $id
 * @property string|null $registration_number
 * @property string|null $bio
 * @property int $specialty_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kid> $kids
 * @property-read int|null $kids_count
 * @property-read \App\Models\Specialty $specialty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $user
 * @property-read int|null $user_count
 * @method static \Illuminate\Database\Eloquent\Builder|Professional newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Professional newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Professional onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Professional query()
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereSpecialtyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Professional withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Professional withoutTrashed()
 */
	class Professional extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProfessionalProfile
 *
 * @property int $id
 * @property int $user_id
 * @property int $specialty_id
 * @property string|null $registration_number
 * @property string|null $bio
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Specialty $specialty
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereSpecialtyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfessionalProfile withoutTrashed()
 */
	class ProfessionalProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Resource
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ability> $abilities
 * @property-read int|null $abilities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Resource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resource query()
 */
	class Resource extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Responsible
 *
 * @property mixed $cell
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kid> $kids
 * @property-read int|null $kids_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\ResponsibleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible query()
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Responsible withoutTrashed()
 */
	class Responsible extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ability> $abilities
 * @property-read int|null $abilities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Role withoutTrashed()
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Specialty
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Professional> $professionals
 * @property-read int|null $professionals_count
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty query()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialty withoutTrashed()
 */
	class Specialty extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $type
 * @property bool $allow
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $phone
 * @property string|null $postal_code
 * @property string|null $street
 * @property string|null $number
 * @property string|null $complement
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $provider_id
 * @property string|null $provider_email
 * @property string|null $provider_avatar
 * @property-read mixed $specialty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kid> $kids
 * @property-read int|null $kids_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Professional> $professional
 * @property-read int|null $professional_count
 * @property-read \App\Models\Role|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User getUsers()
 * @method static \Illuminate\Database\Eloquent\Builder|User listUsers()
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAllow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProviderAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProviderEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

