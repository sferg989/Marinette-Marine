<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/7/2017
 * Time: 5:43 PM
 */
function returnBaanOpenBuySQL($ship_code){
    $sql = "
SELECT	DISTINCT
	b.t_buyr as buyer,
	a.t_cprj as ship_code,
	b.t_cpcp as swbs,
	a.t_item as item,
	b.t_dfit as spn,
	-- Description
		CASE
			WHEN ltrim(a.t_cprj) <> ''
			THEN b.t_dsca
			ELSE c.t_dsca
		END as description,

	a.t_qana as original_smos_qty,
	-- Production Allocations

		CASE
			WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									    and (g.t_koor = 1 or g.t_koor = 8)
									    and t_kotr = 2) is not NULL
			THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									    and (g.t_koor = 1 or g.t_koor = 8)
									    and t_kotr = 2)
			ELSE 0
		END  as production_allocations,

	-- Production Issues

		CASE
			WHEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj and h.t_item = b.t_item
									     and h.t_koor = 1 and h.t_kost = 7) >= 0
			THEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj and h.t_item = b.t_item
									     and h.t_koor = 1 and h.t_kost = 7)
			ELSE 0
		END as production_issues,

	-- Remaining SMOS Qty

		(a.t_qana -
			CASE
				WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj and g.t_item = b.t_item
								and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2) is not NULL
				THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj and g.t_item = b.t_item
								and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2)
				ELSE 0
			END -
			CASE
				WHEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj and h.t_item = b.t_item
										and h.t_koor = 1 and h.t_kost = 7) >= 0
				THEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj and h.t_item = b.t_item
										and h.t_koor = 1 and h.t_kost = 7)
				ELSE 0
			END
		) as remaining_smos_qty,

		a.t_ddat as yard_due_date,

		b.t_oltm as lead_time,

	-- Planned Order Date
		CASE
			WHEN a.t_ddat = '1753-01-01'
				THEN '1753-01-01'
			ELSE cast(a.t_ddat as datetime) - b.t_oltm
		END as plan_order_date,

	-- UOM
		CASE
			WHEN  ltrim(a.t_cprj) <> ''
				THEN b.t_cuni
			ELSE 	c.t_cuni
		END as uom,


		b.t_stoc as cust_item_on_hand,
		b.t_ordr as cust_item_on_order,
		b.t_stoc,
		b.t_ordr,

	-- Total PRP Purch Order Qty
		CASE
			WHEN (select sum(i.t_oqan) from ttipcs520490 i where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										and i.t_osta = 1) is not NULL
			THEN (select sum(i.t_oqan) from ttipcs520490 i where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										and i.t_osta = 1)
			ELSE 0
		END as total_prp_purch_ord_qty,

	-- Total PRP Whse Order Qty
		CASE
			WHEN
				CASE
					WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
								     where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
					WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
								     where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
				END is not NULL
			THEN
				CASE
					WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
								     where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
					WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
								     where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
				END
			ELSE 0
		END as total_prp_whse_ord_qty,

	-- On Order Qty
		CASE
			WHEN
				(b.t_ordr -
					(select sum(i.t_oqan) from ttipcs520490 i
					 where i.t_cprj = a.t_cprj and i.t_item = a.t_item and i.t_osta = 1) -
					CASE
						WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
										where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
						WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
										where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
					END) is not NULL
			THEN
					(b.t_ordr -
					(select sum(i.t_oqan) from ttipcs520490 i
										where i.t_cprj = a.t_cprj and i.t_item = a.t_item and
											i.t_osta = 1) -
					CASE
						WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
										where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
						WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
										where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
					END)
			ELSE 0
		END  as on_order_qty,

	-- Cust Item Shortage
		CASE
			WHEN
				CASE
					WHEN
						(a.t_qana -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
											and g.t_item = b.t_item
											and (g.t_koor = 1 or g.t_koor = 8)
											and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
											and g.t_item = b.t_item
											and (g.t_koor = 1 or g.t_koor = 8)
											and t_kotr = 2)
								ELSE 0
							END -
							CASE
								WHEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj
											and h.t_item = b.t_item
											and h.t_koor = 1 and h.t_kost = 7) >= 0
								THEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj
											and h.t_item = b.t_item
											and h.t_koor = 1 and h.t_kost = 7)
								ELSE 0
							END) >
							(b.t_stoc +
							CASE
								WHEN
									(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										 where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										  and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j where
			j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j where
			j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END) is not NULL
								THEN
										(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										 where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										  and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END)
								ELSE 0
							END -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									 and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									 and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									 and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									 and t_kotr = 2)
								ELSE 0
							END)
					THEN
						(a.t_qana -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									and t_kotr = 2)
								ELSE 0
							END -
							CASE
								WHEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj
									and h.t_item = b.t_item and h.t_koor = 1 and h.t_kost = 7) >= 0
								THEN (select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = b.t_cprj
									and h.t_item = b.t_item and h.t_koor = 1 and h.t_kost = 7)
								ELSE 0
							END) -
							(b.t_stoc +
							CASE
								WHEN
									(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i where
											i.t_cprj = a.t_cprj and i.t_item = a.t_item
											and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END) is not NULL
								THEN
										(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										 where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										 and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		 where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		 where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END)
								ELSE 0
							END -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									 and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									 and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									 and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									 and t_kotr = 2)
								ELSE 0
							END)
				END is not null
			THEN
				CASE
					WHEN
						(a.t_qana -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g where
									g.t_cprj = b.t_cprj and g.t_item = b.t_item and
									(g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = b.t_cprj
									and g.t_item = b.t_item and (g.t_koor = 1 or g.t_koor = 8)
									and t_kotr = 2)
								ELSE 0
							END -
							CASE
								WHEN (select sum(h.t_qstk) from ttdilc301490 h where
									h.t_cprj = b.t_cprj and h.t_item = b.t_item and h.t_koor = 1
									and h.t_kost = 7) >= 0
								THEN (select sum(h.t_qstk) from ttdilc301490 h where
									h.t_cprj = b.t_cprj and h.t_item = b.t_item and h.t_koor = 1
									and h.t_kost = 7)
								ELSE 0
							END) >
							(b.t_stoc +
							CASE
								WHEN
									(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										  where i.t_cprj = a.t_cprj and i.t_item = a.t_item and
										  i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END) is not NULL
								THEN
										(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										 where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										  and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END)
								ELSE 0
							END -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8)
									and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8)
									and t_kotr = 2)
								ELSE 0
							END)
					THEN
						(a.t_qana -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2)
								ELSE 0
							END -
							CASE
								WHEN (select sum(h.t_qstk) from ttdilc301490 h
									where h.t_cprj = b.t_cprj and h.t_item = b.t_item
									and h.t_koor = 1 and h.t_kost = 7) >= 0
								THEN (select sum(h.t_qstk) from ttdilc301490 h
									where h.t_cprj = b.t_cprj and h.t_item = b.t_item
									and h.t_koor = 1 and h.t_kost = 7)
								ELSE 0
							END) -
							(b.t_stoc +
							CASE
								WHEN
									(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
									where i.t_cprj = a.t_cprj and i.t_item = a.t_item
									and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END) is not NULL
								THEN
										(b.t_ordr -
										(select sum(i.t_oqan) from ttipcs520490 i
										 where i.t_cprj = a.t_cprj and i.t_item = a.t_item
										 and i.t_osta = 1) -
										CASE
											WHEN j.t_kood in (2,3) THEN (select sum(j.t_oqan * -1) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
											WHEN j.t_kood = 1 THEN (select sum(j.t_oqan) from ttipcs530490 j
		where j.t_cprj = b.t_cprj and j.t_citm = b.t_item)
										END)
								ELSE 0
							END -
							CASE
								WHEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2) is not NULL
								THEN (select sum(g.t_qana) from ttipcs500490 g
									where g.t_cprj = b.t_cprj and g.t_item = b.t_item
									and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2)
								ELSE 0
							END)
				END
			ELSE 0
		END as cust_item_shortage,

		a.t_hold as on_hold,
		cast(a.t_edon as datetime) as entered_on,
		cast(a.t_lmon as datetime) as last_mod,
		f.t_pric as last_price,
		j.t_cprj,
		j.t_citm,

	-- Expected Amoount

		CASE
			WHEN (a.t_qana -
				(select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = a.t_cprj and g.t_item = a.t_item
						and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2) -
				(select sum(h.t_qstk) from ttdilc301490 h where h.t_cprj = a.t_cprj and h.t_item = a.t_item
						and h.t_koor = 1 and h.t_kost = 7)) >
				(b.t_stoc +
				(b.t_ordr -
				(select sum(i.t_oqan) from ttipcs520490 i where i.t_cprj = a.t_cprj and i.t_item = a.t_item
					and i.t_osta = 1) -
				(select sum(j.t_oqan * -1) from ttipcs530490 j where j.t_cprj = a.t_cprj and j.t_citm = a.t_item
					and j.t_osta not in (4,5)) -
				(select sum(g.t_qana) from ttipcs500490 g where g.t_cprj = a.t_cprj and g.t_item = a.t_item
					and (g.t_koor = 1 or g.t_koor = 8) and t_kotr = 2))) THEN
					(b.t_stoc +
					(b.t_ordr -
					(select sum(i.t_oqan) from ttipcs520490 i
					 where i.t_cprj = a.t_cprj and i.t_item = a.t_item and i.t_osta = 1) -
					(select sum(j.t_oqan * -1) from ttipcs530490 j
				   	 where j.t_cprj = a.t_cprj and j.t_citm = a.t_item and j.t_osta not in (4,5)) -
					(select sum(g.t_qana) from ttipcs500490 g
					 where g.t_cprj = a.t_cprj and g.t_item = a.t_item and (g.t_koor = 1 or g.t_koor = 8)
					  and t_kotr = 2))) * f.t_pric
			ELSE 0
		END as expected_amt,
		a.t_qana as remaining_qty,
		b.t_ordr as on_order_qty,
		b.t_stoc as stock
    FROM	ttiitm901490 a
    LEFT JOIN ttipcs021490 b on b.t_cprj = a.t_cprj and b.t_item = a.t_item
    LEFT JOIN ttiitm001490 c on c.t_item = a.t_item
    LEFT JOIN ttdpur300490 d on d.t_icap = 2 and cast(d.t_edat as DATETIME) > getdate() and cast(d.t_sdat as DATETIME) <= getdate()
    LEFT JOIN ttdpur301490 e on e.t_item = b.t_dfit and e.t_cono = d.t_cono
    LEFT JOIN ttdpur041490 f on f.t_cprj = a.t_cprj and f.t_item = a.t_item
    LEFT JOIN ttipcs520490 i on i.t_cprj = a.t_cprj and i.t_item = a.t_item
    LEFT JOIN ttipcs530490 j on j.t_cprj = b.t_cprj and j.t_citm = b.t_item --and j.t_osta not in (4,5)
    LEFT JOIN ttdinv001490 k on k.t_item = b.t_dfit
    LEFT JOIN ttdilc101490 l on l.t_cwar = k.t_cwar and l.t_item = b.t_dfit
    where a.t_cprj
    like '%$ship_code%'";
    return $sql;
}
function returnBaanOpenPOSQL($ship_code){
    $sql = "
SELECT	distinct a.t_cprj as ship_code,
		CASE
			WHEN convert(INT,a.t_pacn) <> 0 THEN substring(a.t_pacn,2,3)
			ELSE d.t_cpcp
		END as swbs,
		a.t_item as item,
		CASE
			WHEN ltrim(rtrim(a.t_cprj)) <> '' THEN c.t_dsca
			ELSE e.t_dsca
		END as description,
		c.t_n1at as noun_1,
		c.t_n2at as noun_2,
		CASE
			WHEN ltrim(rtrim(a.t_cprj)) <> '      ' THEN
				CASE
					WHEN ltrim(rtrim(c.t_csel)) = 'NR' THEN 'NRE'
					ELSE ''
				END
			ELSE
				CASE
					WHEN ltrim(rtrim(e.t_csel)) = 'NR' THEN 'NRE'
					ELSE ''
				END
		END as nre,
		a.t_suno as vendor,
		a.t_orno as po,
		a.t_pono as line,
		a.t_pric as unit_price,
		a.t_oqua as order_qty,
		a.t_dqua as delivered_qty,
		CASE
			WHEN a.t_dqua <> 0 THEN a.t_bqua
			ELSE a.t_oqua
		END as pending_qty,
		CASE
			WHEN a.t_dqua <> 0 THEN a.t_bqua * a.t_pric
			ELSE a.t_oqua * a.t_pric
		END as pending_amt,
		CASE
			WHEN a.t_ddtc <> a.t_ddtd and a.t_ddtd <> '1753-01-01 00:00:00.000' THEN a.t_ddtd
			ELSE
				CASE
					WHEN a.t_ddta <> a.t_ddtc and a.t_ddtc <> '1753-01-01 00:00:00.000' THEN a.t_ddtc
					ELSE a.t_ddta
				END
		END as delv_date,
		b.t_cpay as payment_terms,
		a.t_pacn as ledger_account
FROM	ttdpur041490 a
		left join ttdpur040490 b on b.t_orno = a.t_orno
		left join ttipcs021490 c on c.t_cprj = a.t_cprj and c.t_item = a.t_item
		left join ttdpur045490 d on (d.t_orno = a.t_orno and d.t_pono = a.t_pono)
		left join ttiitm001490 e on e.t_item = a.t_item
		where 
		a.t_cprj like '%$ship_code%'
ORDER BY
		a.t_cprj, a.t_item";
    return $sql;
}
function loadBaanBuyerIDList(){
    $sql = "
          Select Distinct 
                a.t_buyr buyer_id,
                c.t_nama buyer
          From ttipcs021490 as a
                join ttccom001490 as c on a.t_buyr = c.t_emno
                Order by t_buyr
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "INSERT  into meac.master_buyer (id, buyer) values";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $buyer_id = intval($rs->fields["buyer_id"]);
        $buyer    = $rs->fields["buyer"];
        $sql.=
            "(
                $buyer_id,
                '$buyer'
                ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnGlDetailBaanSQL($wc=""){
    $sql = "
    SELECT	distinct  a.t_dim1 as ship_code,
        a.t_leac as ledger_acct,
        a.t_otyp as transaction_type,
        a.t_odoc as document,
        a.t_olin as line,
        CASE
            WHEN a.t_intt = 1 THEN c.t_item
            ELSE ''
        END as item,
        CASE
            WHEN a.t_intt = 1 THEN
                CASE
                    WHEN ltrim(rtrim(c.t_cprj)) = '' or (c.t_tror = 4 and c.t_fitr = 20) THEN e.t_dsca
                    ELSE f.t_dsca
                END
            ELSE
                CASE
                    WHEN a.t_otyp in ('API','AMI','APC') THEN ''
                    ELSE a.t_refr
                END
        END as description,
        CASE
            WHEN a.t_intt = 1 THEN c.t_orno
            ELSE
                CASE
                    WHEN a.t_otyp = 'API' THEN g.t_orno
                END
        END as order2,
        CASE
            WHEN a.t_intt = 1 THEN c.t_pono
            ELSE
                CASE
                    WHEN a.t_otyp = 'API' THEN 0
                    ELSE a.t_olin
                END
        END as qty,
        CASE
            WHEN a.t_intt = 1 THEN
                CASE
                    WHEN ltrim(rtrim(c.t_suno)) <> '' THEN
                        (SELECT	h.t_nama
                        FROM	ttccom020490 h
                        WHERE	h.t_suno = c.t_suno)
                    WHEN ltrim(rtrim(c.t_suno)) = '' and ltrim(rtrim(c.t_cuno)) <> '' THEN
                        (SELECT	i.t_nama
                        FROM	ttccom010490 i
                        WHERE	i.t_cuno = c.t_cuno)
                END
            ELSE
                CASE
                    WHEN ltrim(rtrim(a.t_suno)) <> '' THEN
                        (SELECT	h.t_nama
                        FROM	ttccom020490 h
                        WHERE	h.t_suno = a.t_suno)
                    WHEN ltrim(rtrim(a.t_suno)) = '' and ltrim(rtrim(a.t_cuno)) <> '' THEN
                        (SELECT	i.t_nama
                        FROM	ttccom010490 i
                        WHERE	i.t_cuno = a.t_cuno)
                END
        END as cust_supp,
        CASE
            WHEN a.t_intt = 1 THEN c.t_nuni
            ELSE 0
        END as qty,
        c.t_cuni as unit,
        CASE
            WHEN a.t_dbcr = 2 THEN 0 - a.t_amth
            ELSE a.t_amth
        END as amt,
        d.t_tedt as date,
        CASE
            WHEN a.t_intt = 1 THEN
                CASE
                    WHEN c.t_dbcr = 2 THEN 0 - c.t_amth
                    ELSE c.t_amth
                END
            ELSE
                CASE
                    WHEN a.t_dbcr = 2 THEN 0 - a.t_amth
                    ELSE a.t_amth
                END 
        END as integr_amt
    FROM	  ttfgld106490 a
    left join ttfgld418490 b on b.t_fcom = a.t_ocmp and b.t_ttyp = a.t_otyp and b.t_docn = a.t_odoc and b.t_lino = a.t_olin
    left join ttfgld410490 c on c.t_ocom = b.t_ocom and c.t_tror = b.t_tror and c.t_fitr = b.t_fitr and c.t_trdt = b.t_trdt
       and c.t_trtm = b.t_trtm and c.t_sern = b.t_sern and c.t_line = b.t_line
    left join ttfgld100490 d on d.t_year = a.t_oyer and d.t_btno = a.t_obat
    left join ttiitm001490 e on e.t_item = c.t_item
    left join ttipcs021490 f on f.t_cprj = c.t_cprj and f.t_item = c.t_item
    left join ttfacp200490 g on g.t_ttyp = a.t_ctyp and g.t_ninv = a.t_cinv and g.t_line = 0 and g.t_tdoc = '' and g.t_docn = 0 and g.t_lino = 0
    WHERE ltrim(rtrim(a.t_leac)) BETWEEN '4000' AND '4999'
      $wc
    ORDER BY a.t_leac, a.t_odoc, a.t_olin
    ";
    /*print $sql;
    die();
    */return $sql;
}
function insertOpenBuyReport($ship_code){
    $i=0;
    $sql = returnBaanOpenBuySQL($ship_code);
    $rs = dbCallBaan($sql);
    $insert_sql = returnOpenBuyInsertSQL();
    $sql = $insert_sql;
    $program = "LCS";
    while (!$rs->EOF)
    {
        $buyer              = trim($rs->fields["buyer"]);
        $ship_code          = trim($rs->fields["ship_code"]);
        $swbs               = trim($rs->fields["swbs"]);
        $item               = trim($rs->fields["swbs"]);
        $spn                = trim($rs->fields["spn"]);
        $description        = addslashes(str_replace("'", " ", trim($rs->fields["description"])));
        $origrinal_smos_qty = formatNumber4decNoComma($rs->fields["original_smos_qty"]);
        $remain_smos_qty    = formatNumber4decNoComma($rs->fields["remain_smos_qty"]);
        $yard_due_date      = fixExcelDateMySQL($rs->fields["yard_due_date"]);
        $lead_time          = $rs->fields["lead_time"];
        $plan_order_date    = fixExcelDateMySQL($rs->fields["plan_order_date"]);
        $uom                = trim($rs->fields["uom"]);
        $item_on_hand       = formatNumber4decNoComma($rs->fields["item_on_hand"]);
        $item_on_order      = formatNumber4decNoComma($rs->fields["item_on_order"]);
        $item_shortage      = formatNumber4decNoComma($rs->fields["item_shortage"]);
        $on_hold            = $rs->fields["on_hold"];
        $entered_on         = fixExcelDateMySQL($rs->fields["entered_on"]);
        $last_mod           = fixExcelDateMySQL($rs->fields["last_mod"]);
        $last_price         = formatNumber4decNoComma($rs->fields["last_price"]);
        $expected_amt       = formatNumber4decNoComma($rs->fields["expected_amt"]);
        $stock              = formatNumber4decNoComma($rs->fields["stock"]);
        $on_order_qty       = formatNumber4decNoComma($rs->fields["on_order_qty"]);
        $remaining_qty      = formatNumber4decNoComma($rs->fields["remaining_qty"]);

        $sql.= " (
            '$program',
            $ship_code,
            '$buyer',
            '$swbs',
            '$item',
            '$spn',
            '$description',
            '$origrinal_smos_qty',
            '$remain_smos_qty',
            '$yard_due_date',
            '$lead_time',
            '$plan_order_date',
            '$uom',
            '$item_on_hand',
            '$item_on_order',
            '$item_shortage',
            '$on_hold',
            '$entered_on',
            '$last_mod',
            '$last_price',
            '$expected_amt'
        ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
function returnOpenBuyInsertSQL(){
    $insert_sql = "
    insert into meac.open_buy (
        program,
        ship_code,
        buyer,
        swbs,
        item,
        spn,
        description,
        origrinal_smos_qty,
        remain_smos_qty,
        yard_due_date,
        lead_time,
        plan_order_date,
        uom,
        item_on_hand,
        item_on_order,
        item_shortage,
        on_hold,
        entered_on,
        last_mod,
        last_price,
        expected_amt) VALUES ";
    return $insert_sql;
}

function returnOpenPOInsertSQL(){
    $insert_sql = "
        insert into meac.open_po (
            ship_code,
            swbs,
            item,
            description,
            noun_1,
            noun_2,
            nre,
            vendor,
            po,
            line,
            unit_price,
            order_qty,
            delivered_qty,
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea 
    ) VALUES 
       ";
    return $insert_sql;
}
function insertOpenPOReport($ship_code){
    $sql = returnBaanOpenPOSQL($ship_code);
    $rs = dbCallBaan($sql);
    $insert_sql= returnOpenPOInsertSQL();
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $ship_code     = intval($rs->fields["ship_code"]);
        $swbs          = intval($rs->fields["swbs"]);
        $item          = addslashes(trim($rs->fields["item"]));
        $description   = addslashes(trim($rs->fields["description"]));
        $noun_1        = addslashes(trim($rs->fields["noun_1"]));
        $noun_2        = addslashes(trim($rs->fields["noun_2"]));
        $nre           = addslashes(trim($rs->fields["nre"]));
        $vendor        = intval($rs->fields["vendor"]);
        $po            = intval($rs->fields["po"]);
        $line          = intval($rs->fields["line"]);
        $unit_price    = formatNumber4decNoComma($rs->fields["unit_price"]);
        $order_qty     = formatNumber4decNoComma($rs->fields["order_qty"]);
        $delivered_qty = formatNumber4decNoComma($rs->fields["delivered_qty"]);
        $pending_qty   = formatNumber4decNoComma($rs->fields["pending_qty"]);
        $pending_amnt  = formatNumber4decNoComma($rs->fields["pending_amnt"]);
        $delv_date     = fixExcelDateMySQL($rs->fields["delv_date"]);
        $payment_terms = intval($rs->fields["payment_terms"]);
        $ledger_acct   = intval($rs->fields["ledger_acct"]);
        $clin          = addslashes(trim($rs->fields["clin"]));
        $effort        = addslashes(trim($rs->fields["effort"]));
        $ecp_rea       = trim($rs->fields["ecp_rea"]);

        $sql.=
            "(
                $ship_code,
                $swbs,
                '$item',
                '$description',
                '$noun_1',
                '$noun_2',
                '$nre',
                $vendor,
                $po,
                $line,
                $unit_price,
                $order_qty,
                $delivered_qty,
                $pending_qty,
                $pending_amnt,
                '$delv_date',
                $payment_terms,
                $ledger_acct,
                '$clin',
                '$effort',
                '$ecp_rea'
                ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}

function returnGlDetailInsertSQL(){
    $insert_sql = "
        INSERT  INTO meac.gl_detail (
            ldger_acct,
            document,
            line,
            item,
            description,
            `order`,
            pos,
            cust_supp,
            qty,
            unit,
            amt,
            date,
            integr_amt,
            clin,
            effort,
            ship_code, 
            ecp_rea) 
            values
           ";
    return $insert_sql;
}
function loadGlDetailBaan($ship_code="", $rpt_period=""){
    if($rpt_period!=""){
        $year      = intval(substr($rpt_period, 0, 4));
        $month     = month2digit(substr($rpt_period, -2));
        $period_wc = "AND a.t_fyer <=$year AND	a.t_fprd <=$month and ";
    }
    if($ship_code!=""){
        if($rpt_period!=""){
            $period_wc = substr($period_wc, 0, -5);
        }
        $ship_code_wc = "AND a.t_dim1 = '$ship_code'";

    }
    $wc = $period_wc." ".$ship_code_wc;

    $sql        = returnGlDetailBaanSQL($wc);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnGlDetailInsertSQL();
    $sql        = $insert_sql;
    $i=0;
    while (!$rs->EOF)
    {
        $ldger_acct  = intval($rs->fields["ledger_acct"]);
        $document    = addslashes(trim($rs->fields["document"]));
        $line        = intval($rs->fields["line"]);
        $item        = addslashes(trim($rs->fields["item"]));
        $description = addslashes(trim($rs->fields["description"]));
        $order       = intval($rs->fields["order2"]);
        $pos         = intval($rs->fields["line"]);
        $cust_supp   = addslashes(trim($rs->fields["cust_supp"]));
        $qty         = formatNumber4decNoComma($rs->fields["qty"]);
        $unit        = addslashes(trim($rs->fields["unit"]));
        $amt         = formatNumber4decNoComma($rs->fields["amt"]);
        $date        = fixExcelDateMySQL($rs->fields["date"]);
        $integr_amt  = formatNumber4decNoComma($rs->fields["integr_amt"]);
        $clin        = addslashes(trim($rs->fields["clin"]));
        $effort      = addslashes(trim($rs->fields["effort"]));
        $ecp_rea     = addslashes(trim($rs->fields["ecp_rea"]));
        $ship_code   = intval($rs->fields["ship_code"]);

        $sql.=
            "(
                $ldger_acct,
                '$document',
                $line,
                '$item',
                '$description',
                $order,
                $pos,
                '$cust_supp',
                $qty,
                '$unit',
                $amt,
                '$date',
                $integr_amt,
                '$clin',
                '$effort',
                $ship_code,
                '$ecp_rea'
                ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnFortisPOSQL($ship_code=""){
    $wc = "where Project_Number  <> ''";
    if($ship_code!=""){
        $wc = " and Project_Number = '$ship_code'";
    }

    $sql = "select
                project_number,
                po_number,
                supplier_name,
                supplier_number,
                notes,
                buyer_name,
                purchase_order_type,
                funding_source,
                total_amount,
                vendor_project_total,
                order_date,
                created_date,
                modified_date,
                (case
                when _cont.Container = 'Project Approved' or _cont.Container = 'Approved MRO' Then 'Approved'
                when _cont.Container = 'Purchase Orders Disapproved' Then 'Denied'
                when _cont.Container like '%Pending%' or _cont.Container like '%New PO%' Then 'Pending'
                when _cont.Container = 'No Approval' Then 'Approved'
                when _cont.Container like '%Complete%' Then 'Approved'
                when _cont.Container like '%Denied%' Then 'Denied'
                when _cont.Container like '%Pending%' Then 'Pending'
                when _cont.Container = 'New PO' Then 'New' else '' end)
                as fortisstatus
                from FMM_Purchase_Order
                left outer join FTBContainer _cont on _cont.Container_ID = F_ParentID
                $wc
    ";
    return $sql;
}
function returnFortisPOInsertSQL(){
    $insert_sql = "
    INSERT  into po_data (
        ship_code,
        po,
        vendor,
        vendor_id,
        notes,
        buyer,
        po_type,
        funding_source,
        amt,
        vendor_total,
        status,
        program,
        order_date,
        created_date,
        modified_date)  
      VALUES 
    ";
    return $insert_sql;
}
function returnInsertPODATAInsertSQL($ship_code,$po,$vendor,$vendor_id,
                                     $notes,$buyer,$po_type,$funding_source,
                                     $amt,$vendor_total,$status,$program,$order_date,
                                     $created_date,$modified_date)
{
    $sql = "(
        $ship_code,
        $po,
        '$vendor',
        $vendor_id,
        '$notes',
        '$buyer',
        '$po_type',
        '$funding_source',
        $amt,
        $vendor_total,
        '$status',
        '$program',
        '$order_date',
        '$created_date',
        '$modified_date'),";
    return $sql;
}
function loadFortisPOData($ship_code= ""){
    $sql        = returnFortisPOSQL($ship_code);
    $rs         = dbCallFortis($sql);
    $insert_sql = returnFortisPOInsertSQL();
    $sql        = $insert_sql;

    $program = "LCS";
    $i = 0;
    while (!$rs->EOF)
    {
        $ship_code      = intval(trim($rs->fields["project_number"]));
        $po             = intval(trim($rs->fields["po_number"]));
        $vendor         = processDescription(trim($rs->fields["supplier_name"]));
        $vendor_id      = intval(trim($rs->fields["supplier_number"]));
        $notes          = processDescription(trim($rs->fields["notes"]));
        $buyer          = processDescription(trim($rs->fields["buyer_name"]));
        $po_type        = trim($rs->fields["purchase_order_type"]);
        $funding_source = trim($rs->fields["funding_source"]);
        $status         = trim($rs->fields["fortisstatus"]);
        $order_date     = $rs->fields["order_date"];
        $created_date   = $rs->fields["created_date"];
        $modified_date  = $rs->fields["modified_date"];
        $amt            = formatNumber4decNoComma($rs->fields["total_amount"]);
        $vendor_total   = formatNumber4decNoComma($rs->fields["vendor_project_total"]);

        $sql.=returnInsertPODATAInsertSQL($ship_code,$po,$vendor,$vendor_id,
            $notes,$buyer,$po_type,$funding_source,
            $amt,$vendor_total,$status,$program, $order_date,
            $created_date, $modified_date);

        if($i == 2000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=2000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    //print $sql;
}

function loadResponsibleBuyer($ship_code){
    $sql = "
         select t_cprj, t_buyr, t_item  from ttipcs021490 where t_cprj like '%$ship_code%'
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "insert into buyer_reponsible (ship_code, buyer_id,item) VALUES";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $ship_code = intval(trim($rs->fields["t_cprj"]));
        $buyer     = trim($rs->fields["t_buyr"]);
        $item      = trim($rs->fields["t_item"]);
        $sql.=
            "(
                $ship_code,
                $buyer,
                '$item'
                ),";
        if($i == 2000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=2000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnBaanEFDBSQL($ship_code){
    $sql = "
    Select 
        a.t_cprj as project,
        b.t_item as item,
        b.t_cmod as module,
        b.t_revi as efdb_change,
        b.t_nqan as change_quantity,
        CONVERT(CHAR(10), a.t_revd, 101) as change_date,
        a.t_revr as reason_code,
        c.t_dsca description,
        CASE WHEN a.t_appr=1 Then 'Yes' WHEN a.t_appr=2 Then 'No' Else 'No' END as hdr_processed,
        CASE WHEN b.t_proc=1 Then 'Yes' WHEN b.t_proc=2 Then 'No' Else 'No' END as dtl_processed
        From		ttifct030490 as a
        Left Join	ttifct035490 as b on a.t_cprj = b.t_cprj and a.t_revi = b.t_revi
        Left Join	ttisfc902490 as c on c.t_rwrk = a.t_revr
        Where
      a.t_cprj like '%$ship_code%'
        Order by a.t_cprj, b.t_item, b.t_cmod, b.t_revi DESC
      ";
    return $sql;
}
function returnBaanEFDBInsert(){
    $sql = "INSERT  into change_item (
            ship_code,
            item,
            module,
            efdb_change,
            change_qty,
            date,
            reason_code,
            description, 
            hdr_processed, 
            dtl_processed) VALUES 
            ";
    return $sql;
}
function insertEFDBChange($ship_code,$item, $module,
                          $efdb_change, $change_qty, $date,
                          $reason_code, $description, $hdr_processed,$dtl_processed){
    $sql = "(
            $ship_code,
            '$item',
            '$module',
            '$efdb_change',
            '$change_qty',
            '$date',
            '$reason_code',
            '$description',
            '$hdr_processed',
            '$dtl_processed'),";
    return $sql;
}

function loadEFDBChangeBAAN($ship_code){
    $sql        = returnBaanEFDBSQL($ship_code);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnBaanEFDBInsert();
    $sql        = $insert_sql;
    $i=0;
    while (!$rs->EOF)
    {

        $ship_code     = intval($rs->fields["project"]);
        $item          = addslashes(trim($rs->fields["item"]));
        $module        = addslashes(trim($rs->fields["module"]));
        $efdb_change   = addslashes(trim($rs->fields["efdb_change"]));
        $change_qty    = formatNumber4decNoComma(trim($rs->fields["change_quantity"]));
        $date          = fixExcelDateMySQL($rs->fields["change_date"]);
        $reason_code   = addslashes(trim($rs->fields["reason_code"]));
        $description   = addslashes(trim($rs->fields["description"]));
        $hdr_processed = addslashes(trim($rs->fields["hdr_processed"]));
        $dtl_processed = addslashes(trim($rs->fields["dtl_processed"]));

        $sql.= insertEFDBChange($ship_code,$item, $module,$efdb_change,
                $change_qty, $date,$reason_code, $description,
                $hdr_processed,$dtl_processed);
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}