<?php

namespace App\Console\Commands;

use GraphQL\Client;
use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Extraction1OrganizationsSponsorFieldsCommand extends Command
{
    protected $signature = 'extraction-1';

    public function handle(): int
    {
        // query {
        //  __schema {
        //    types {
        //      name
        //      kind
        //      description
        //      fields {
        //        name
        //      }
        //    }
        //  }
        // }

        // query {
        //  __type(name: "Repository") {
        //    name
        //    kind
        //    description
        //    fields {
        //      name
        //    }
        //  }
        // }

        $gql = (new Query('__schema'))
            ->setSelectionSet([
                (new Query('types'))
                    ->setSelectionSet(
                        [
                            'name',
                            'kind',
                            'description',
                        ]
                    ),
            ]);

        $gql = (new Query('__type'))
            ->setArguments(['name' => 'Organization'])
            ->setSelectionSet([
                'name',
                'kind',
                'description',
                (new Query('fields'))
                    ->setSelectionSet([
                        'name',
                    ]),
            ]);

        $organizationQuery = (new Query('organization'))
            ->setArguments(['login' => 'freecodecamp'])
            ->setSelectionSet(
                $organizationSelectionSet = [
                    'id',
                    'name',
                    'login',
                    'estimatedNextSponsorsPayoutInCents',
                    'hasSponsorsListing',
                    //                'isSponsoredBy',
                    //                'isSponsoringViewer',
                    (new Query('lifetimeReceivedSponsorshipValues'))
                        ->setArguments(['last' => 5])
                        ->setSelectionSet([
                            (new Query('edges'))
                                ->setSelectionSet([
                                    (new Query('node'))
                                        ->setSelectionSet([
                                            'amountInCents',
                                            //                                        'sponsor',
                                        ]),
                                ]),
                            (new Query('nodes'))
                                ->setSelectionSet([
                                    'amountInCents',
                                    //                                        'sponsor',
                                ]),
                        ]),
                    'monthlyEstimatedSponsorsIncomeInCents',
                    (new Query('sponsoring'))
                        ->setArguments(['last' => 5])
                        ->setSelectionSet([
                            (new Query('edges'))
                                ->setSelectionSet([
                                    (new Query('node'))
                                        ->setSelectionSet([
                                            (new InlineFragment('User'))
                                                ->setSelectionSet([
                                                    'name',
                                                    //                                        'sponsor',
                                                ]),
                                            (new InlineFragment('Organization'))
                                                ->setSelectionSet([
                                                    'name',
                                                    //                                        'sponsor',
                                                ]),
                                        ]),
                                ]),
                        ]),
                    (new Query('sponsors'))
                        ->setArguments(['last' => 100])
                        ->setSelectionSet([
                            (new Query('edges'))
                                ->setSelectionSet([
                                    (new Query('node'))
                                        ->setSelectionSet([
                                            (new InlineFragment('User'))
                                                ->setSelectionSet([
                                                    'name',
                                                    (new Query('lifetimeReceivedSponsorshipValues'))
                                                        ->setArguments(['last' => 100])
                                                        ->setSelectionSet([
                                                            (new Query('edges'))
                                                                ->setSelectionSet([
                                                                    (new Query('node'))
                                                                        ->setSelectionSet([
                                                                            'amountInCents',
                                                                            //                                        'sponsor',
                                                                        ]),
                                                                ]),
                                                            (new Query('nodes'))
                                                                ->setSelectionSet([
                                                                    'amountInCents',
                                                                    //                                        'sponsor',
                                                                ]),
                                                        ]),
                                                    //                                                'amountInCents',
                                                    //                                        'sponsor',
                                                ]),
                                            (new InlineFragment('Organization'))
                                                ->setSelectionSet([
                                                    'name',
                                                    //                                        'sponsor',
                                                ]),
                                        ]),
                                ]),
                        ]),
                    (new Query('sponsorsActivities'))
                        ->setArguments(['last' => 100])
                        ->setSelectionSet([
                            (new Query('nodes'))
                                ->setSelectionSet([
                                    'action',
                                    'currentPrivacyLevel',
                                    'id',
                                    'paymentSource',
                                    //                                        'previousSponsorsTier',
                                    //                                        'sponsorsTier',
                                    'viaBulkSponsorship',
                                ]),
                            (new Query('edges'))
                                ->setSelectionSet([
                                    (new Query('node'))
                                        ->setSelectionSet([
                                            'action',
                                            'currentPrivacyLevel',
                                            'id',
                                            'paymentSource',
                                            //                                        'previousSponsorsTier',
                                            //                                        'sponsorsTier',
                                            'viaBulkSponsorship',
                                        ]),
                                ]),
                        ]),
                    (new Query('sponsorsListing'))
                        ->setSelectionSet([
                            (new Query('activeGoal'))
                                ->setSelectionSet([
                                    'percentComplete',
                                ]),
                            //                        ( new Query('activeStripeAccount') )
                            //                            ->setSelectionSet([
                            //                                'id',
                            //                            ]),
                            'billingCountryOrRegion',
                            (new Query('tiers'))
                                ->setArguments(['last' => 100])
                                ->setSelectionSet([
                                    (new Query('nodes'))
                                        ->setSelectionSet([
                                            'id',
                                            'description',
                                            'isCustomAmount',
                                            'isOneTime',
                                            'monthlyPriceInCents',
                                            'monthlyPriceInDollars',
                                            'name',
                                        ]),
                                ]),
                        ]),

                    //                ( new Query('sponsorshipForViewerAsSponsor') )
                    //                    ->setArguments()
                    //                    ->setSelectionSet([
                    //                        ( new Query('edges') )
                    //                            ->setSelectionSet([
                    //                                ( new Query('node') )
                    //                                    ->setSelectionSet([
                    //                                        ( new InlineFragment('User') )
                    //                                            ->setSelectionSet([
                    //                                                'name',
                    //                                                ( new Query('lifetimeReceivedSponsorshipValues') )
                    //                                                    ->setArguments(['last' => 100])
                    //                                                    ->setSelectionSet([
                    //                                                        ( new Query('edges') )
                    //                                                            ->setSelectionSet([
                    //                                                                ( new Query('node') )
                    //                                                                    ->setSelectionSet([
                    //                                                                        'amountInCents',
                    //                                                                        //                                        'sponsor',
                    //                                                                    ]),
                    //                                                            ]),
                    //                                                        ( new Query('nodes') )
                    //                                                            ->setSelectionSet([
                    //                                                                'amountInCents',
                    //                                                                //                                        'sponsor',
                    //                                                            ]),
                    //                                                    ]),
                    //                                                //                                                'amountInCents',
                    //                                                //                                        'sponsor',
                    //                                            ]),
                    //                                        ( new InlineFragment('Organization') )
                    //                                            ->setSelectionSet([
                    //                                                'name',
                    //                                                //                                        'sponsor',
                    //                                            ]),
                    //                                    ]),
                    //                            ]),
                    //                    ]),
                    //                ( new Query('sponsorshipForViewerAsSponsorable') )
                    //                    ->setArguments(['last' => 100])
                    //                    ->setSelectionSet([
                    //                        ( new Query('edges') )
                    //                            ->setSelectionSet([
                    //                                ( new Query('node') )
                    //                                    ->setSelectionSet([
                    //                                        ( new InlineFragment('User') )
                    //                                            ->setSelectionSet([
                    //                                                'name',
                    //                                                ( new Query('lifetimeReceivedSponsorshipValues') )
                    //                                                    ->setArguments(['last' => 100])
                    //                                                    ->setSelectionSet([
                    //                                                        ( new Query('edges') )
                    //                                                            ->setSelectionSet([
                    //                                                                ( new Query('node') )
                    //                                                                    ->setSelectionSet([
                    //                                                                        'amountInCents',
                    //                                                                        //                                        'sponsor',
                    //                                                                    ]),
                    //                                                            ]),
                    //                                                        ( new Query('nodes') )
                    //                                                            ->setSelectionSet([
                    //                                                                'amountInCents',
                    //                                                                //                                        'sponsor',
                    //                                                            ]),
                    //                                                    ]),
                    //                                                //                                                'amountInCents',
                    //                                                //                                        'sponsor',
                    //                                            ]),
                    //                                        ( new InlineFragment('Organization') )
                    //                                            ->setSelectionSet([
                    //                                                'name',
                    //                                                //                                        'sponsor',
                    //                                            ]),
                    //                                    ]),
                    //                            ]),
                    //                    ]),
                    (new Query('sponsorshipsAsMaintainer'))
                        ->setArguments(['last' => 100])
                        ->setSelectionSet([
                            'totalRecurringMonthlyPriceInCents',
                            'totalRecurringMonthlyPriceInDollars',
                            (new Query('nodes'))
                                ->setSelectionSet([
                                    'id',
                                    'isActive',
                                    'isOneTimePayment',
                                    'isSponsorOptedIntoEmail',
                                    'paymentSource',
                                    'privacyLevel',
                                    (new Query('tier'))
                                        ->setSelectionSet([
                                            'id',
                                        ]),
                                ]),
                        ]),
                    (new Query('sponsorshipsAsSponsor'))
                        ->setArguments(['last' => 100])
                        ->setSelectionSet([
                            'totalRecurringMonthlyPriceInCents',
                            'totalRecurringMonthlyPriceInDollars',
                            (new Query('nodes'))
                                ->setSelectionSet([
                                    'id',
                                    'isActive',
                                    'isOneTimePayment',
                                    'isSponsorOptedIntoEmail',
                                    'paymentSource',
                                    'privacyLevel',
                                    (new Query('tier'))
                                        ->setSelectionSet([
                                            'id',
                                        ]),
                                ]),
                        ]),
                    (new Query('sponsorshipNewsletters'))
                        ->setArguments(['last' => 100])
                        ->setSelectionSet([
                            (new Query('nodes'))
                                ->setSelectionSet([
                                    'body',
                                ]),
                        ]),
                    //                'sponsorshipForViewerAsSponsor',
                    //                'sponsorshipForViewerAsSponsorable',
                    //                'sponsorshipNewsletters',
                    //                'sponsorshipsAsMaintainer',
                    //                'sponsorshipsAsSponsor',
                    'totalSponsorshipAmountAsSponsorInCents',
                    'viewerCanSponsor',
                    //                (new Query('lifetimeReceivedSponsorshipValues'))
                    //                ->setSelectionSet([
                    //                    'amountInCents',
                    //                    'sponsor'
                    //                ]),
                ]
            );

        $organizationsQuery = (new Query('search'))
            ->setArguments([
                'query' => 'type:org is:sponsorable',
                'first' => 100,
                'type' => new RawObject('USER'),
                'after' => null,
            ])
            ->setSelectionSet([
                'userCount',
                (new Query('pageInfo'))
                    ->setSelectionSet([
                        'startCursor',
                        'hasNextPage',
                        'endCursor',
                    ]),
                (new Query('nodes'))
                    ->setSelectionSet([
                        (new InlineFragment('Organization'))
                            ->setSelectionSet([
                                'name',
                                'login',
                            ]),
                    ]),
            ]);

        $client = new Client(
            config('services.git_hub.endpoint'),
            ['Authorization' => 'Bearer ' . config('services.git_hub.personal_access_token')]
        );

        $responseBody = $client->runQuery($organizationsQuery)->getResults();

        $endCursor = null;

        do {
            if ($endCursor) {
                $responseBody = $client->runQuery(
                    $organizationsQuery->setArguments([...invade($organizationsQuery)->arguments, 'after' => $endCursor])
                )->getResults();
            }

            if ($endCursor) {
                foreach ($responseBody->data->search->nodes as $node) {
                    $result = json_decode(json_encode($client->runQuery($organizationQuery->setArguments(['login' => $node->login]))->getData()), true);

                    $organization = $result['organization'];

                    DB::table('extraction_1_organizations_sponsor_fields')->updateOrInsert([
                        'login' => $organization['login'],
                    ], [
                        'gitHubId' => $organization['id'],
                        'name' => $organization['name'],
                        'estimatedNextSponsorsPayoutInCents' => $organization['estimatedNextSponsorsPayoutInCents'],
                        'hasSponsorsListing' => $organization['hasSponsorsListing'],
                        'lifetimeReceivedSponsorshipValues' => json_encode($organization['lifetimeReceivedSponsorshipValues']),
                        'monthlyEstimatedSponsorsIncomeInCents' => $organization['monthlyEstimatedSponsorsIncomeInCents'],
                        'sponsoring' => json_encode($organization['sponsoring']),
                        'sponsors' => json_encode($organization['sponsors']),
                        'sponsorsListing' => json_encode($organization['sponsorsListing']),
                        'sponsorshipsAsMaintainer' => json_encode($organization['sponsorshipsAsMaintainer']),
                        'sponsorshipsAsSponsor' => json_encode($organization['sponsorshipsAsSponsor']),
                        'sponsorshipNewsletters' => json_encode($organization['sponsorshipNewsletters']),
                        'totalSponsorshipAmountAsSponsorInCents' => $organization['totalSponsorshipAmountAsSponsorInCents'],
                        'viewerCanSponsor' => $organization['viewerCanSponsor'],
                        'updated_at' => now(),
                    ]);
                }
            }

            $endCursor = $responseBody->data->search->pageInfo->endCursor;
        } while ($responseBody->data->search->pageInfo->hasNextPage);

        dd($responseBody);

        return static::SUCCESS;
    }
}
