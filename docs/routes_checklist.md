# Rotas Checklist

| Método    | URI                                                          | Nome                               | Controller/Ação                             |
| --------- | ------------------------------------------------------------ | ---------------------------------- | ------------------------------------------- |
| GET/HEAD  | api/checklistregisters                                       | checklistregisters.index           | Api\ChecklistRegisterController@index       |
| POST      | api/checklistregisters                                       | checklistregisters.store           | Api\ChecklistRegisterController@store       |
| GET/HEAD  | api/checklistregisters/progressbar/{checklist_id}/{level_id} | api.checklistregisters.progressbar | Api\ChecklistRegisterController@progressbar |
| GET/HEAD  | api/checklistregisters/{checklistregister}                   | checklistregisters.show            | Api\ChecklistRegisterController@show        |
| PUT/PATCH | api/checklistregisters/{checklistregister}                   | checklistregisters.update          | Api\ChecklistRegisterController@update      |
| DELETE    | api/checklistregisters/{checklistregister}                   | checklistregisters.destroy         | Api\ChecklistRegisterController@destroy     |
| GET/HEAD  | api/checklists                                               | checklists.index                   | Api\ChecklistController@index               |
| POST      | api/checklists                                               | checklists.store                   | Api\ChecklistController@store               |
| GET/HEAD  | api/checklists/{checklist}                                   | checklists.show                    | Api\ChecklistController@show                |
| PUT/PATCH | api/checklists/{checklist}                                   | checklists.update                  | Api\ChecklistController@update              |
| DELETE    | api/checklists/{checklist}                                   | checklists.destroy                 | Api\ChecklistController@destroy             |
| GET/HEAD  | checklists                                                   | checklists.index                   | ChecklistController@index                   |
| POST      | checklists                                                   | checklists.store                   | ChecklistController@store                   |
| GET/HEAD  | checklists/create                                            | checklists.create                  | ChecklistController@create                  |
| GET/HEAD  | checklists/datatable/index                                   | checklists.index_data              | ChecklistController@index_data              |
| GET/HEAD  | checklists/register                                          | checklists.register                | ChecklistController@register                |
| GET/HEAD  | checklists/{checklist}                                       | checklists.show                    | ChecklistController@show                    |
| PUT/PATCH | checklists/{checklist}                                       | checklists.update                  | ChecklistController@update                  |
| DELETE    | checklists/{checklist}                                       | checklists.destroy                 | ChecklistController@destroy                 |
| GET/HEAD  | checklists/{checklist}/edit                                  | checklists.edit                    | ChecklistController@edit                    |
| GET/HEAD  | checklists/{id}/chart                                        | checklists.chart                   | ChecklistController@chart                   |
| GET/HEAD  | checklists/{id}/clonar                                       | checklists.clonar                  | ChecklistController@clonarChecklist         |
| GET/HEAD  | checklists/{id}/fill                                         | checklists.fill                    | ChecklistController@fill                    |
