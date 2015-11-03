<?php

class ShowController extends StudipController
{

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox.php'));
//      PageLayout::setTitle('');

        URLHelper::bindLinkParam('stg', $stg2);
        URLHelper::bindLinkParam('abs', $abs2);
        URLHelper::bindLinkParam('from', $from2);
        URLHelper::bindLinkParam('semstart', $a);
        URLHelper::bindLinkParam('semend', $b);
    }

    public function index_action()
    {
        $from = strtotime('Last Monday', strtotime(Request::get('from', time())));
        $to = strtotime('Next Sunday', $from);
        $stg =  Request::get('stg', 'all');
        $abs =  Request::get('abs', 'all');
        $semstart =  Request::get('semsstart', 0);
        $semend =  Request::get('semend', 99);

        // Set conditions
        if ($stg != 'all') {
            $where[] = ' studiengang_id = :stg ';
        }

        if ($abs != 'all') {
            $where[] = ' abschluss_id = :abs ';
        }

        $where[] = ' semester BETWEEN :semstart AND :semend ';
        $where[] = " termine.date BETWEEN :from AND :to
    AND
    termine.end_time BETWEEN :from AND :to ";

        $whereConditions = join(' AND ', $where);

        $sql = "SELECT COUNT(*) as count,termine.* FROM user_studiengang
JOIN seminar_user USING (user_id)
JOIN termine ON (termine.range_id = seminar_user.seminar_id)
WHERE $whereConditions GROUP BY termin_id";
        $stmt = DBManager::get()->prepare($sql);

        // Bind parameter
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
        $stmt->bindParam(':stg', $stg);
        $stmt->bindParam(':abs', $abs);
        $stmt->bindParam(':semstart', $semstart);
        $stmt->bindParam(':semend', $semend);

        // Only query if we got at least stg or abs
        if (($stg || $abs) && ($stg != 'all' || $abs != 'all')) {
            $stmt->execute();
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                for ($i = $data['date']; $i < $data['end_time']; $i += 3600) {
                    $this->power[strftime('%u', $i)][(int) strftime('%H', $i)] += $data['count'];
                    $this->maxpower = max(array($this->maxpower, $this->power[strftime('%u', $i)][(int) strftime('%H', $i)]));
                }
            }
        }

        // Set sidebar
        $sidebar = Sidebar::Get();

        // Studycourse
        $stgFilter = new SelectWidget(_('Studiengang'), $this->url_for('show/index'), 'stg');
        $stgFilter->addElement(new SelectElement('all', _('Alle')), 'select-all');
        foreach (StudyCourse::findBySQL('1=1 ORDER BY name') as $studycourse) {
            $stgFilter->addElement(new SelectElement($studycourse->id, $studycourse->name, Request::get('stg') == $studycourse->id), 'select-' . $studycourse->id);
        }
        $sidebar->addWidget($stgFilter);

        // Abschluss
        $absFilter = new SelectWidget(_('Abschluss'), $this->url_for('show/index'), 'abs');
        $absFilter->addElement(new SelectElement('all', _('Alle')), 'select-all');
        foreach (Degree::findBySQL('1=1 ORDER BY name') as $deg) {
            $absFilter->addElement(new SelectElement($deg->id, $deg->name, Request::get('abs') == $deg->id), 'select-' . $deg->id);
        }
        $sidebar->addWidget($absFilter);

        // Semester
        $semesterChooser = new SearchWidget($this->url_for('show/index'));
        $semesterChooser->setTitle(_('Semester'));
        $semesterChooser->addNeedle(_('Von'), 'semstart', true);
        $semesterChooser->addNeedle(_('Bis'), 'semend', true);
        $sidebar->addWidget($semesterChooser);

        // Week
        $search = new SearchWidget($this->url_for('show/index'));
        $search->setTitle(_('Kalenderwoche'));
        $search->addNeedle(_('Kalenderwoche'), 'from', true);
        $sidebar->addWidget($search);
    }

    // customized #url_for for plugins
    function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
}
