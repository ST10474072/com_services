<?php
namespace Jbaylet\Component\Services\Site\View\Item;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

/**
 * HTML Article View class for the Services component
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item object
     *
     * @var  \JObject
     */
    protected $item;

    /**
     * The reviews for this item
     *
     * @var  array
     */
    protected $reviews;

    /**
     * Related services
     *
     * @var  array
     */
    protected $relatedServices;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The application parameters
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        try {
            // Get data from the model
            $this->item = $this->get('Item');
            $this->reviews = $this->get('Reviews');
            $this->relatedServices = $this->get('RelatedServices');
            $this->state = $this->get('State');
            $this->params = $this->state->get('params');

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                throw new GenericDataException(implode("\n", $errors), 500);
            }

            // Check if the item exists
            if (!$this->item) {
                throw new \Exception(Text::_('COM_SERVICES_ERROR_ITEM_NOT_FOUND'), 404);
            }

            // Set page metadata
            $this->setDocumentMetadata();

            parent::display($tpl);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                // Redirect to items list
                Factory::getApplication()->redirect(Route::_('index.php?option=com_services&view=items'), $e->getMessage(), 'error');
                return false;
            } else {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                return false;
            }
        }
    }

    /**
     * Set document metadata
     *
     * @return  void
     */
    protected function setDocumentMetadata()
    {
        $document = Factory::getDocument();

        if ($this->item) {
            // Set page title
            $title = $this->item->title;
            if (!empty($this->item->location)) {
                $title .= ' - ' . $this->item->location;
            }
            $document->setTitle($title);

            // Set meta description
            if (!empty($this->item->description)) {
                $metaDesc = strip_tags($this->item->description);
                $metaDesc = substr($metaDesc, 0, 160);
                $document->setDescription($metaDesc);
            }

            // Add structured data for SEO
            $this->addStructuredData();

            // Set canonical URL
            $canonical = Route::_('index.php?option=com_services&view=item&id=' . $this->item->id . ':' . $this->item->alias);
            $document->addHeadLink($canonical, 'canonical');
        }
    }

    /**
     * Add structured data for better SEO
     *
     * @return  void
     */
    protected function addStructuredData()
    {
        if (!$this->item) {
            return;
        }

        $document = Factory::getDocument();

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $this->item->title,
            'description' => strip_tags($this->item->description ?? ''),
        ];

        // Add address if available
        if (!empty($this->item->location)) {
            $structuredData['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $this->item->location
            ];
        }

        // Add geo coordinates if available
        if (!empty($this->item->lat) && !empty($this->item->lng)) {
            $structuredData['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $this->item->lat,
                'longitude' => $this->item->lng
            ];
        }

        // Add rating if available
        if (!empty($this->item->rating_avg) && $this->item->rating_avg > 0) {
            $structuredData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->item->rating_avg,
                'ratingCount' => $this->item->reviews_count ?? 0
            ];
        }

        // Add contact info if available
        if (!empty($this->item->phone)) {
            $structuredData['telephone'] = $this->item->phone;
        }

        if (!empty($this->item->email)) {
            $structuredData['email'] = $this->item->email;
        }

        if (!empty($this->item->website)) {
            $structuredData['url'] = $this->item->website;
        }

        // Add opening hours if 24/7
        if ($this->item->is_247) {
            $structuredData['openingHoursSpecification'] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                ],
                'opens' => '00:00',
                'closes' => '23:59'
            ];
        }

        $document->addScriptDeclaration('
        var structuredData = ' . json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';
        var script = document.createElement("script");
        script.type = "application/ld+json";
        script.text = JSON.stringify(structuredData);
        document.head.appendChild(script);
        ');
    }
}