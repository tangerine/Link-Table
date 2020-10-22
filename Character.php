public function getData($filter = null)
{
    // Enable debug mode if we are in our development environment.
    // This will return the Editor's SQL statements in the JSON data 
    // as the property "debugSql".
    // DO NOT ENABLE debug() mode in production version.
    $debug = false;
    if ( Kohana::$environment === Kohana::DEVELOPMENT ) {
        $debug = true;
    }

    // Build our Editor instance and process the data coming from _POST.
    $editor = Editor::inst($GLOBALS['db'], 'Characters', 'character_id')

    // In Editor instances that use a join, fully-qualified field names 
    // are required (tablename.fieldname) in field instances.
        ->fields(
            Field::inst('Characters.character_id')->set(false),
            Field::inst('Characters.last_name')->validator( 'Validate::notEmpty' )
                // Use a custom validation method through a closure.
                // This function tests for leading and/or trailing spaces.
                ->validator( 'Validate::spaces' ),
            Field::inst('Characters.name_prefix')->validator( 'Validate::spaces' ),
            Field::inst('Characters.first_name')->validator( 'Validate::spaces' ),
            Field::inst('Characters.name_suffix')->validator( 'Validate::spaces' ),
            Field::inst('Characters.name_for_display'),  

            Field::inst('StageShows.stageshow_id')->setFormatter( Format::ifEmpty(null) )                            
                    ->validator( 'Validate::required' )
                    ->options( Options::inst()
                        ->table( 'StageShows' )
                        ->value( 'stageshow_id' )
                        ->label( 'title_for_display' )  
                        ->order( 'title_body ASC' )
            ),
            Field::inst( 'StageShows.title_for_display' ),
            Field::inst( 'StageShows.title_body' ) 

        ) // fields  
        ->leftJoin( 'StageShows_Characters', 'Characters.character_id', '=', 'StageShows_Characters.character_id' )
        ->leftJoin( 'StageShows', 'StageShows_Characters.stageshow_id', '=', 'StageShows.stageshow_id' )
        
        // Global validator to prevent deletion of certain records.
        ->validator( function ( $editor, $action, $data ) {                       
            if ( $action === Editor::ACTION_DELETE ) {      
               
                foreach ( $data['data'] as $pkey => $values ) {
                    foreach ($values as $key => $val) {
                        if ('character_id' == $key) {
                            if ($this->hasConnections('id', $val, 'Characters')) {
                                return 'Cannot delete. Character id appears in other tables.';
                            }
                        }
                    }
                }
            } // if ( $action === Editor::ACTION_DELETE )
        }) // Global validator                                                                          

        ->on( 'preCreate', function ( $editor, &$values ) {
            $this->buildNameForDisplay($editor, $values);
            })
        ->on( 'preEdit', function ( $editor, $id, &$values ) {
            $this->buildNameForDisplay($editor, $values);
            }); 

        if ($filter) {
            if ( ! is_array($filter)) {
                switch ($filter) {
                    case 'nac' :
                        $editor->where( function ( $q ) {
                            $q->where('Characters.character_id', "(SELECT character_id FROM StageProductions_Actors WHERE character_id IS NOT null)", 'NOT IN', false );
                        });
                    break;
                    case 'tu' :
                        $editor->where( function ( $q ) {
                            $q->where( 'DATE(Characters.last_update)', "DATE(NOW())", '=', false );
                        });
                    break;
                    default:
                        // What?
                    break;
                }
            } else {
                // $filter is an array.
                switch ( key($filter)) {
                    case 'character_id':
                        $character_id = $filter['character_id'] ;
                        $editor->where( function ( $q ) use ( $character_id) {
                            $q->where( 'Characters.character_id', $character_id );
                        });   
                    break;
                    default:
                        // What?
                    break;
                } // switch ($key($filter))
   
            } // else
               
        } // if ($filter) {

       $editor->debug($debug)
        ->process( $_POST ) 
        ->json();

    exit;
                                                                                    
} // public function getData() 